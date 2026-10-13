<?php

namespace Tests\Unit;

use App\Services\GoogleSheetsService;
use App\Support\AdmissionFields;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Exercises the real header-writing code paths (no HTTP) by injecting a stub
 * Google Sheets client, so we know worksheets created by the system always get
 * the admission header row in the correct order.
 */
class GoogleSheetsHeaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['google_sheets.spreadsheet_id' => 'test-spreadsheet']);
    }

    private object $recorder;

    /**
     * @param  list<list<string|null>>  $rows  what readRows() should return
     */
    private function serviceWithValues(array $rows): GoogleSheetsService
    {
        $recorder = $this->recorder = new class($rows)
        {
            /** @var list<array{range: string, values: list<list<string|null>>}> */
            public array $writes = [];

            /** @param list<list<string|null>> $rows */
            public function __construct(private array $rows) {}

            public function get(string $spreadsheetId, string $range, array $optParams = []): ValueRange
            {
                return new ValueRange(['values' => $this->rows]);
            }

            public function update(string $spreadsheetId, string $range, ValueRange $body, array $optParams = []): ValueRange
            {
                $this->writes[] = ['range' => $range, 'values' => $body->getValues()];

                return new ValueRange(['values' => $body->getValues()]);
            }
        };

        $sheets = new Sheets([]);
        $sheets->spreadsheets_values = $recorder;

        $service = new GoogleSheetsService;

        $property = new ReflectionProperty(GoogleSheetsService::class, 'sheets');
        $property->setAccessible(true);
        $property->setValue($service, $sheets);

        return $service;
    }

    private function setWorksheetMeta(GoogleSheetsService $service, string $worksheet): void
    {
        $property = new ReflectionProperty(GoogleSheetsService::class, 'worksheetMeta');
        $property->setAccessible(true);
        $property->setValue($service, [$worksheet => ['id' => 1, 'index' => 0]]);
    }

    private function invoke(GoogleSheetsService $service, string $method, string $worksheet): void
    {
        $reflection = new ReflectionMethod(GoogleSheetsService::class, $method);
        $reflection->setAccessible(true);
        $reflection->invoke($service, $worksheet);
    }

    public function test_write_headers_uses_the_thirty_admission_fields_in_order(): void
    {
        $service = $this->serviceWithValues([]);

        $this->invoke($service, 'writeHeaders', 'Class 1 - A');

        $this->assertCount(1, $this->recorder->writes);
        $this->assertSame("'Class 1 - A'!A1", $this->recorder->writes[0]['range']);
        $this->assertSame([AdmissionFields::headers()], $this->recorder->writes[0]['values']);
        $this->assertCount(30, $this->recorder->writes[0]['values'][0]);
    }

    public function test_empty_worksheet_is_repaired_with_headers(): void
    {
        $service = $this->serviceWithValues([]);
        $this->setWorksheetMeta($service, 'Class 1 - A');

        $this->invoke($service, 'writeHeadersIfEmpty', 'Class 1 - A');

        $this->assertCount(1, $this->recorder->writes);
        $this->assertSame([AdmissionFields::headers()], $this->recorder->writes[0]['values']);
    }

    public function test_existing_content_is_never_overwritten(): void
    {
        $service = $this->serviceWithValues([['custom', 'header']]);
        $this->setWorksheetMeta($service, 'Class 1 - A');

        $this->invoke($service, 'writeHeadersIfEmpty', 'Class 1 - A');

        $this->assertSame([], $this->recorder->writes);
    }
}
