<?php

namespace App\Jobs;

use App\Models\Section;
use App\Services\WorksheetSynchronizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Retryable background synchronisation of a section's worksheet.
 *
 * Dispatched when the immediate (synchronous) sync fails, for example because
 * Google Sheets is temporarily unavailable. Laravel retries the job with a
 * backoff; the section's sync status reflects the latest attempt either way.
 */
class SyncSectionWorksheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(public readonly int $sectionId) {}

    public function handle(WorksheetSynchronizer $synchronizer): void
    {
        $section = Section::find($this->sectionId);

        if ($section === null) {
            return;
        }

        // A thrown GoogleSheetsException is intentionally not swallowed: it lets
        // the queue retry the job with the configured backoff.
        $synchronizer->sync($section);
    }

    public function failed(?Throwable $exception): void
    {
        Section::find($this->sectionId)?->markSyncFailed(
            $exception?->getMessage() ?? 'Worksheet synchronisation failed after repeated attempts.'
        );
    }
}
