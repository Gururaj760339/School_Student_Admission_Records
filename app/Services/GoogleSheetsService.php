<?php

namespace App\Services;

use App\Exceptions\GoogleSheetsException;
use App\Support\AdmissionClasses;
use App\Support\AdmissionFields;
use Google\Client as GoogleClient;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\DeleteDimensionRequest;
use Google\Service\Sheets\DeleteSheetRequest;
use Google\Service\Sheets\DimensionRange;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\UpdateSheetPropertiesRequest;
use Google\Service\Sheets\ValueRange;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Read/write access to the student admission spreadsheet.
 *
 * Google Sheets is the ONLY persistent store for student records; this service
 * never touches a database. Spreadsheet metadata, worksheet titles and sheet
 * ids are cached per instance so a single request performs the minimum number
 * of API calls.
 *
 * The service can also create, rename and delete worksheets (tabs) and write
 * the admission header row into new worksheets. These operations are always
 * idempotent and never overwrite existing data:
 *   - creating a worksheet that already exists is a no-op;
 *   - renaming refuses to overwrite an existing worksheet;
 *   - deleting is only ever attempted on an empty worksheet.
 */
class GoogleSheetsService
{
    private ?Sheets $sheets = null;

    /** @var array<string, array{id: int, index: int}>|null */
    private ?array $worksheetMeta = null;

    /**
     * Resolve the absolute, sanitised path to the service-account JSON file.
     *
     * Relative paths are resolved against the Laravel storage/base path and are
     * never served from the public directory.
     */
    public function credentialsPath(): string
    {
        $configured = (string) config('google_sheets.credentials_path');
        $normalized = str_replace('\\', '/', trim($configured));

        if ($normalized === '') {
            $normalized = 'storage/app/google/service-account.json';
        }

        if ($this->isAbsolutePath($normalized)) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'storage/')) {
            return storage_path(substr($normalized, strlen('storage/')));
        }

        return base_path($normalized);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || (bool) preg_match('#^[A-Za-z]:[\\\\/]#', $path);
    }

    public function spreadsheetId(): string
    {
        $id = trim((string) config('google_sheets.spreadsheet_id'));

        if ($id === '') {
            throw GoogleSheetsException::notConfigured();
        }

        return $id;
    }

    /**
     * Build an authenticated Google client that reads the private key only
     * from the configured local JSON file.
     */
    private function client(): GoogleClient
    {
        $path = $this->credentialsPath();

        if (! is_file($path)) {
            throw GoogleSheetsException::credentialsMissing($path);
        }

        if (! is_readable($path)) {
            throw GoogleSheetsException::credentialsUnreadable($path);
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw GoogleSheetsException::credentialsUnreadable($path);
        }

        $credentials = json_decode($contents, true);

        // Never echo the file contents; only report structural validity.
        unset($contents);

        if (! is_array($credentials)
            || empty($credentials['client_email'])
            || empty($credentials['private_key'])) {
            throw GoogleSheetsException::credentialsInvalid();
        }

        try {
            $client = new GoogleClient;
            $client->setApplicationName((string) config('google_sheets.application_name'));
            $client->setScopes(config('google_sheets.scopes', [Sheets::SPREADSHEETS]));
            $client->setAuthConfig($path);

            if (class_exists(Client::class)) {
                $timeout = (int) config('google_sheets.request_timeout', 30);
                $client->setHttpClient(new Client([
                    'timeout' => $timeout,
                    'connect_timeout' => min(10, $timeout),
                ]));
            }

            return $client;
        } catch (GoogleSheetsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw GoogleSheetsException::credentialsInvalid();
        }
    }

    private function service(): Sheets
    {
        if ($this->sheets instanceof Sheets) {
            return $this->sheets;
        }

        try {
            $this->sheets = new Sheets($this->client());
        } catch (GoogleSheetsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw GoogleSheetsException::authenticationFailed($e);
        }

        return $this->sheets;
    }

    /**
     * Wrap an API call so every failure is converted to a safe exception.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function call(string $operation, callable $callback)
    {
        try {
            return $callback();
        } catch (GoogleSheetsException $e) {
            throw $e;
        } catch (GoogleServiceException $e) {
            $code = (int) $e->getCode();
            $reason = $this->firstErrorReason($e);

            if ($code === 401 || in_array($reason, ['authError', 'invalid_grant', 'unauthorized_client'], true)) {
                throw GoogleSheetsException::authenticationFailed($e);
            }

            if ($code === 404) {
                throw GoogleSheetsException::spreadsheetUnavailable($e);
            }

            if ($code === 403) {
                throw new GoogleSheetsException(
                    'Google Sheets denied access while '.$operation.'. Confirm the spreadsheet is shared with the '
                    .'service account ('.$this->safeServiceAccountEmail().') as Editor, and that the Sheets API is enabled.',
                    previous: $e
                );
            }

            throw GoogleSheetsException::api($operation, $e);
        } catch (Throwable $e) {
            throw GoogleSheetsException::api($operation, $e);
        }
    }

    private function firstErrorReason(GoogleServiceException $e): string
    {
        $errors = $e->getErrors() ?? [];

        return (string) ($errors[0]['reason'] ?? '');
    }

    private function safeServiceAccountEmail(): string
    {
        $email = (string) config('google_sheets.service_account_email');

        // The service-account email is public information (it is a "share with"
        // address), never a secret. Still avoid returning anything odd.
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'the configured service account';
    }

    /**
     * Worksheet metadata keyed by title: ['id' => int, 'index' => int].
     *
     * @return array<string, array{id: int, index: int}>
     */
    public function worksheetMeta(): array
    {
        if ($this->worksheetMeta !== null) {
            return $this->worksheetMeta;
        }

        $spreadsheet = $this->call('reading spreadsheet metadata', function () {
            return $this->service()->spreadsheets->get(
                $this->spreadsheetId(),
                ['fields' => 'properties.title,sheets(properties(sheetId,title,index))']
            );
        });

        $meta = [];

        foreach ($spreadsheet->getSheets() as $sheet) {
            $properties = $sheet->getProperties();

            if (! $properties) {
                continue;
            }

            $meta[(string) $properties->getTitle()] = [
                'id' => (int) $properties->getSheetId(),
                'index' => (int) $properties->getIndex(),
            ];
        }

        if ($meta === []) {
            throw new GoogleSheetsException('The configured spreadsheet contains no worksheets.');
        }

        return $this->worksheetMeta = $meta;
    }

    /**
     * @return list<string>
     */
    public function worksheetTitles(): array
    {
        return array_keys($this->worksheetMeta());
    }

    public function spreadsheetTitle(): string
    {
        $spreadsheet = $this->call('reading spreadsheet metadata', function () {
            return $this->service()->spreadsheets->get(
                $this->spreadsheetId(),
                ['fields' => 'properties.title']
            );
        });

        return (string) $spreadsheet->getProperties()->getTitle();
    }

    public function assertWorksheetExists(string $worksheet): void
    {
        if (! array_key_exists($worksheet, $this->worksheetMeta())) {
            throw GoogleSheetsException::worksheetMissing($worksheet);
        }
    }

    public function worksheetExists(string $worksheet): bool
    {
        return array_key_exists($worksheet, $this->worksheetMeta());
    }

    /**
     * Ensure a worksheet exists, creating it (with the admission header row)
     * when it does not. Safe to call repeatedly: an existing worksheet is never
     * modified or duplicated.
     *
     * @return string one of "exists" or "created"
     */
    public function ensureWorksheet(string $worksheet): string
    {
        $lock = Cache::lock('google-sheets:create:'.sha1($worksheet), 30);

        return $lock->block(15, function () use ($worksheet) {
            if ($this->worksheetExists($worksheet)) {
                // A previous attempt may have created the tab but failed before
                // writing its header row. Repair an empty worksheet so retries
                // are safe; never touch a worksheet that already has content.
                $this->writeHeadersIfEmpty($worksheet);

                return 'exists';
            }

            $addSheet = new SheetsRequest([
                'addSheet' => new AddSheetRequest([
                    'properties' => new SheetProperties(['title' => $worksheet]),
                ]),
            ]);

            $this->call('creating worksheet "'.$worksheet.'"', function () use ($addSheet) {
                return $this->service()->spreadsheets->batchUpdate(
                    $this->spreadsheetId(),
                    new BatchUpdateSpreadsheetRequest(['requests' => [$addSheet]])
                );
            });

            // Invalidate cached metadata so subsequent calls see the new tab.
            $this->worksheetMeta = null;

            // A new worksheet is empty; write the exact admission header row so
            // every section shares the same column structure and order.
            $this->writeHeaders($worksheet);

            return 'created';
        });
    }

    /**
     * Write the admission header row into an empty worksheet. Refuses to touch
     * a worksheet that already contains any values, so no existing data or
     * custom headers are ever overwritten.
     */
    private function writeHeadersIfEmpty(string $worksheet): void
    {
        if ($this->readRows($worksheet) !== []) {
            return;
        }

        $this->writeHeaders($worksheet);
    }

    private function writeHeaders(string $worksheet): void
    {
        $range = $this->quoteWorksheet($worksheet).'!A1';

        $this->call('writing headers to "'.$worksheet.'"', function () use ($range) {
            return $this->service()->spreadsheets_values->update(
                $this->spreadsheetId(),
                $range,
                new ValueRange(['values' => [AdmissionFields::headers()]]),
                ['valueInputOption' => 'RAW']
            );
        });
    }

    /**
     * Rename a worksheet, preserving all of its data.
     *
     * Never overwrites an existing worksheet: if the target already exists the
     * operation fails loudly so an administrator can resolve the conflict.
     *
     * @return string one of "unchanged", "already", "created" or "renamed"
     */
    public function renameWorksheet(string $from, string $to): string
    {
        if ($from === $to) {
            return 'unchanged';
        }

        $meta = $this->worksheetMeta();

        if (! isset($meta[$from])) {
            return isset($meta[$to]) ? 'already' : $this->ensureWorksheet($to);
        }

        if (isset($meta[$to])) {
            throw new GoogleSheetsException(
                'Cannot rename worksheet "'.$from.'" to "'.$to.'": a worksheet named "'.$to
                .'" already exists. Rename or remove the existing worksheet manually, then retry.'
            );
        }

        $request = new SheetsRequest([
            'updateSheetProperties' => new UpdateSheetPropertiesRequest([
                'properties' => new SheetProperties([
                    'sheetId' => $meta[$from]['id'],
                    'title' => $to,
                ]),
                'fields' => 'title',
            ]),
        ]);

        $this->call('renaming worksheet "'.$from.'" to "'.$to.'"', function () use ($request) {
            return $this->service()->spreadsheets->batchUpdate(
                $this->spreadsheetId(),
                new BatchUpdateSpreadsheetRequest(['requests' => [$request]])
            );
        });

        $this->worksheetMeta = null;

        return 'renamed';
    }

    /**
     * Whether a worksheet contains any populated student rows (beyond the
     * header). Used to protect existing data before a worksheet is removed.
     */
    public function worksheetHasRecords(string $worksheet): bool
    {
        $rows = $this->readRows($worksheet);

        for ($i = 1; $i < count($rows); $i++) {
            if (! $this->rowIsEmpty($rows[$i])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a worksheet. Callers must ensure it is empty first (see
     * {@see worksheetHasRecords()}); this method never deletes student data
     * silently because it refuses to run on a populated worksheet.
     *
     * @return string one of "missing" or "deleted"
     */
    public function deleteWorksheet(string $worksheet): string
    {
        $meta = $this->worksheetMeta();

        if (! isset($meta[$worksheet])) {
            return 'missing';
        }

        if ($this->worksheetHasRecords($worksheet)) {
            throw new GoogleSheetsException(
                'Worksheet "'.$worksheet.'" still contains student records and was not deleted.'
            );
        }

        $request = new SheetsRequest([
            'deleteSheet' => new DeleteSheetRequest(['sheetId' => $meta[$worksheet]['id']]),
        ]);

        $this->call('deleting worksheet "'.$worksheet.'"', function () use ($request) {
            return $this->service()->spreadsheets->batchUpdate(
                $this->spreadsheetId(),
                new BatchUpdateSpreadsheetRequest(['requests' => [$request]])
            );
        });

        $this->worksheetMeta = null;

        return 'deleted';
    }

    private function quoteWorksheet(string $worksheet): string
    {
        return "'".str_replace("'", "''", $worksheet)."'";
    }

    private function lastColumn(): string
    {
        return $this->columnLetter(count(AdmissionFields::headers()));
    }

    private function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    /**
     * Read all raw rows (list of cell lists) for a worksheet, header included.
     *
     * @return list<list<string|null>>
     */
    public function readRows(string $worksheet): array
    {
        $this->assertWorksheetExists($worksheet);

        $range = $this->quoteWorksheet($worksheet).'!A1:'.$this->lastColumn();

        $response = $this->call('reading worksheet "'.$worksheet.'"', function () use ($range) {
            return $this->service()->spreadsheets_values->get(
                $this->spreadsheetId(),
                $range,
                ['valueRenderOption' => 'FORMATTED_VALUE']
            );
        });

        $values = $response->getValues() ?? [];

        return array_map(fn ($row) => array_map(
            fn ($cell) => $this->cleanFromSheets($cell),
            $row
        ), $values);
    }

    /**
     * Validate the header row. Empty worksheets are tolerated for reads.
     *
     * @param  list<list<string|null>>  $rows
     */
    public function assertHeadersValid(string $worksheet, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->assertHeaderRowMatches($worksheet, $rows[0]);
    }

    /**
     * @param  list<string|null>  $actual
     */
    private function assertHeaderRowMatches(string $worksheet, array $actual): void
    {
        $expected = AdmissionFields::headers();

        $normalized = array_map(static function ($value) {
            $value = is_string($value) ? trim($value) : '';

            // The worksheet header row may mark required columns with a
            // trailing " *"; compare against the bare field name only.
            return preg_replace('/ \*$/', '', $value) ?? $value;
        }, $actual);
        $normalized = array_slice($normalized, 0, count($expected));

        if ($normalized !== $expected) {
            throw GoogleSheetsException::headersMismatch($worksheet, $expected, $actual);
        }
    }

    /**
     * Verify that the worksheet already has a valid header row.
     *
     * This check only validates; it never writes. Worksheets created by the
     * system (see {@see ensureWorksheet()}) already carry the admission header
     * row. A worksheet with no header row, or with a mismatched one, raises a
     * setup error rather than guessing, so existing data is always preserved.
     */
    public function assertHeaders(string $worksheet): void
    {
        $rows = $this->readRows($worksheet);

        if ($rows === []) {
            throw GoogleSheetsException::headersMissing($worksheet);
        }

        $this->assertHeaderRowMatches($worksheet, $rows[0]);
    }

    /**
     * All populated records for a worksheet, each augmented with:
     *   - "record_id": a synthetic identifier equal to the physical row number
     *     (worksheets have no record_id column)
     *   - "_row": the 1-based spreadsheet row number
     *
     * @return list<array<string, string|null>>
     */
    public function getRecords(string $worksheet): array
    {
        $rows = $this->readRows($worksheet);
        $this->assertHeadersValid($worksheet, $rows);

        if (count($rows) < 2) {
            return [];
        }

        $headers = AdmissionFields::headers();
        $records = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $record = [];

            foreach ($headers as $column => $header) {
                $value = $row[$column] ?? null;
                $record[$header] = ($value === '' || $value === null) ? null : (string) $value;
            }

            $rowNumber = $i + 1;
            $record['_row'] = $rowNumber;
            $record[AdmissionFields::RECORD_ID] = (string) $rowNumber;
            $records[] = $record;
        }

        return $records;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Records for several classes, each tagged with its class slug and name.
     * Every worksheet is read at most once per call.
     *
     * @param  list<string>  $slugs
     * @return list<array<string, string|null>>
     */
    public function getRecordsForClasses(array $slugs): array
    {
        $all = [];

        foreach ($slugs as $slug) {
            $worksheet = AdmissionClasses::worksheetFor($slug);

            if ($worksheet === null) {
                continue;
            }

            foreach ($this->getRecords($worksheet) as $record) {
                $record['_class_slug'] = $slug;
                $record['_class_name'] = AdmissionClasses::nameFor($slug);
                $all[] = $record;
            }
        }

        return $all;
    }

    public function findRecord(string $worksheet, string $recordId): ?array
    {
        foreach ($this->getRecords($worksheet) as $record) {
            if ((string) ($record[AdmissionFields::RECORD_ID] ?? '') === (string) $recordId) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Append a new record and return it with its synthetic row-based record_id.
     *
     * The worksheet must already contain the header row (worksheets created by
     * the system get one automatically via {@see ensureWorksheet()}); the header
     * row itself is validated and never modified by this method.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    public function appendRecord(string $worksheet, array $data): array
    {
        $this->assertHeaders($worksheet);

        $normalized = AdmissionFields::normalize($data);
        $row = $this->buildRow($normalized);
        $range = $this->quoteWorksheet($worksheet).'!A1';

        $response = $this->call('appending a record to "'.$worksheet.'"', function () use ($range, $row) {
            return $this->service()->spreadsheets_values->append(
                $this->spreadsheetId(),
                $range,
                new ValueRange(['values' => [$row]]),
                ['valueInputOption' => 'RAW', 'insertDataOption' => 'INSERT_ROWS']
            );
        });

        $updates = $response->getUpdates();
        $updatedRows = (int) ($updates?->getUpdatedRows() ?? 0);

        if ($updatedRows < 1) {
            throw new GoogleSheetsException(
                'Google Sheets did not confirm the new record in "'.$worksheet.'". Nothing was saved; please retry.'
            );
        }

        $recordId = $this->rowFromRange($updates?->getUpdatedRange())
            ?? $this->latestRowNumber($worksheet);

        return $normalized + [AdmissionFields::RECORD_ID => $recordId];
    }

    /**
     * Update an existing record identified by its record_id (row number).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    public function updateRecord(string $worksheet, string $recordId, array $data): array
    {
        $record = $this->findRecord($worksheet, $recordId);

        if ($record === null) {
            throw GoogleSheetsException::recordNotFound($recordId);
        }

        $rowNumber = (int) $record['_row'];
        $normalized = AdmissionFields::normalize($data);
        $row = $this->buildRow($normalized);

        $range = $this->quoteWorksheet($worksheet).'!A'.$rowNumber.':'.$this->lastColumn().$rowNumber;

        $response = $this->call('updating a record in "'.$worksheet.'"', function () use ($range, $row) {
            return $this->service()->spreadsheets_values->update(
                $this->spreadsheetId(),
                $range,
                new ValueRange(['values' => [$row]]),
                ['valueInputOption' => 'RAW']
            );
        });

        $updatedRows = (int) ($response->getUpdatedRows() ?? 0);

        if ($updatedRows < 1) {
            throw new GoogleSheetsException(
                'Google Sheets did not confirm the update in "'.$worksheet.'". The record may be unchanged; please retry.'
            );
        }

        return $normalized + [AdmissionFields::RECORD_ID => (string) $rowNumber];
    }

    /**
     * Delete only the record matching record_id (its physical row).
     */
    public function deleteRecord(string $worksheet, string $recordId): void
    {
        $meta = $this->worksheetMeta();

        if (! isset($meta[$worksheet])) {
            throw GoogleSheetsException::worksheetMissing($worksheet);
        }

        // Re-read immediately before deleting and verify the target row really
        // holds this record_id (guards against stale/shifted rows).
        $record = $this->findRecord($worksheet, $recordId);

        if ($record === null) {
            throw GoogleSheetsException::recordNotFound($recordId);
        }

        $rowNumber = (int) $record['_row'];

        $request = new SheetsRequest([
            'deleteDimension' => new DeleteDimensionRequest([
                'range' => new DimensionRange([
                    'sheetId' => $meta[$worksheet]['id'],
                    'dimension' => 'ROWS',
                    'startIndex' => $rowNumber - 1,
                    'endIndex' => $rowNumber,
                ]),
            ]),
        ]);

        $response = $this->call('deleting a record from "'.$worksheet.'"', function () use ($request) {
            return $this->service()->spreadsheets->batchUpdate(
                $this->spreadsheetId(),
                new BatchUpdateSpreadsheetRequest(['requests' => [$request]])
            );
        });

        // Confirm the row is actually gone now.
        if ($this->findRecord($worksheet, $recordId) !== null) {
            throw new GoogleSheetsException(
                'Google Sheets did not confirm deletion of the record. It may still exist; refresh and retry.'
            );
        }

        unset($response);
    }

    /**
     * @param  array<string, string|null>  $data
     * @return list<string>
     */
    private function buildRow(array $data): array
    {
        $row = [];

        foreach (AdmissionFields::names() as $name) {
            $row[] = $this->sanitizeForSheets($data[$name] ?? null);
        }

        return $row;
    }

    /**
     * Extract the 1-based row number from an A1 range such as
     * "'Class 1'!A5:AD5".
     */
    private function rowFromRange(?string $range): ?string
    {
        if (! is_string($range) || $range === '') {
            return null;
        }

        if (preg_match('/![A-Za-z]+(\d+)/', $range, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Fallback row lookup for the most recently appended record.
     */
    private function latestRowNumber(string $worksheet): string
    {
        $records = $this->getRecords($worksheet);

        if ($records === []) {
            return '2';
        }

        $last = end($records);

        return (string) ($last['_row'] ?? '2');
    }

    /**
     * Neutralise values that a spreadsheet could interpret as formulas while
     * preserving them as text. RAW input already stores values literally; this
     * is a defence in depth for values that leave Sheets (e.g. CSV export).
     */
    private function sanitizeForSheets(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Reverse {@see sanitizeForSheets()} for values this app wrote, so what the
     * user sees round-trips exactly.
     */
    private function cleanFromSheets(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if (strlen($value) >= 2 && $value[0] === "'" && in_array($value[1], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return substr($value, 1);
        }

        return $value;
    }
}
