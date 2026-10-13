<?php

namespace Tests\Support;

use App\Exceptions\GoogleSheetsException;
use App\Services\GoogleSheetsService;
use App\Support\AdmissionClasses;

/**
 * In-memory stand-in for the real Google Sheets service, so the HTTP layer can
 * be exercised without touching the Google API or any credentials.
 */
class FakeGoogleSheetsService extends GoogleSheetsService
{
    /** @var list<array<string, mixed>> */
    public array $records = [];

    /** @var list<array{worksheet: string, data: array<string, mixed>}> */
    public array $appended = [];

    /** @var list<array{worksheet: string, record_id: string, data: array<string, mixed>}> */
    public array $updated = [];

    /** @var list<array{worksheet: string, record_id: string}> */
    public array $deleted = [];

    public ?GoogleSheetsException $throwOnRead = null;

    /** @var array<string, bool> worksheet name => exists */
    public array $worksheets = [];

    /** @var list<string> */
    public array $createdWorksheets = [];

    /** @var list<array{from: string, to: string}> */
    public array $renamedWorksheets = [];

    /** @var list<string> */
    public array $deletedWorksheets = [];

    /** @var array<string, bool> worksheet name => contains student records */
    public array $worksheetsWithRecords = [];

    public ?GoogleSheetsException $throwOnSync = null;

    public function worksheetExists(string $worksheet): bool
    {
        return $this->worksheets[$worksheet] ?? false;
    }

    public function ensureWorksheet(string $worksheet): string
    {
        if ($this->throwOnSync) {
            throw $this->throwOnSync;
        }

        if ($this->worksheetExists($worksheet)) {
            return 'exists';
        }

        $this->worksheets[$worksheet] = true;
        $this->createdWorksheets[] = $worksheet;

        return 'created';
    }

    public function renameWorksheet(string $from, string $to): string
    {
        if ($this->throwOnSync) {
            throw $this->throwOnSync;
        }

        if ($from === $to) {
            return 'unchanged';
        }

        unset($this->worksheets[$from]);
        $this->worksheets[$to] = true;
        $this->renamedWorksheets[] = ['from' => $from, 'to' => $to];

        return 'renamed';
    }

    public function worksheetHasRecords(string $worksheet): bool
    {
        if ($this->throwOnSync) {
            throw $this->throwOnSync;
        }

        return $this->worksheetsWithRecords[$worksheet] ?? false;
    }

    public function deleteWorksheet(string $worksheet): string
    {
        if ($this->throwOnSync) {
            throw $this->throwOnSync;
        }

        if (! $this->worksheetExists($worksheet)) {
            return 'missing';
        }

        unset($this->worksheets[$worksheet]);
        $this->deletedWorksheets[] = $worksheet;

        return 'deleted';
    }

    /** @return list<array<string, mixed>> */
    public function getRecordsForClasses(array $slugs): array
    {
        if ($this->throwOnRead) {
            throw $this->throwOnRead;
        }

        return array_values(array_filter(
            $this->records,
            fn ($record) => in_array($record['_class_slug'] ?? null, $slugs, true)
        ));
    }

    /** @return list<array<string, mixed>> */
    public function getRecords(string $worksheet): array
    {
        if ($this->throwOnRead) {
            throw $this->throwOnRead;
        }

        return array_values(array_filter(
            $this->records,
            fn ($record) => AdmissionClasses::worksheetFor($record['_class_slug'] ?? null) === $worksheet
        ));
    }

    public function findRecord(string $worksheet, string $recordId): ?array
    {
        if ($this->throwOnRead) {
            throw $this->throwOnRead;
        }

        foreach ($this->records as $record) {
            if (($record['record_id'] ?? null) === $recordId) {
                return $record;
            }
        }

        return null;
    }

    public function appendRecord(string $worksheet, array $data): array
    {
        $recordId = 'generated-'.(count($this->appended) + 1);
        $data['record_id'] = $recordId;

        $this->appended[] = ['worksheet' => $worksheet, 'data' => $data];

        return $data;
    }

    public function updateRecord(string $worksheet, string $recordId, array $data): array
    {
        $data['record_id'] = $recordId;
        $this->updated[] = ['worksheet' => $worksheet, 'record_id' => $recordId, 'data' => $data];

        return $data;
    }

    public function deleteRecord(string $worksheet, string $recordId): void
    {
        $this->deleted[] = ['worksheet' => $worksheet, 'record_id' => $recordId];
    }
}
