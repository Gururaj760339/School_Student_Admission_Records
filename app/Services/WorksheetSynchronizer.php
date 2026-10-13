<?php

namespace App\Services;

use App\Exceptions\GoogleSheetsException;
use App\Models\Section;

/**
 * Keeps a section's Google Sheets worksheet in sync with its database record.
 *
 * Synchronisation is retryable and idempotent:
 *   - a brand new section gets a worksheet created with the admission headers;
 *   - a renamed section gets its worksheet renamed (existing rows preserved);
 *   - re-running a completed sync is a no-op that does not create duplicates.
 *
 * Every outcome is recorded on the section (status, error, timestamp) so the
 * admin panel can surface failures and offer a retry. The database write is
 * never rolled back because Google Sheets is temporarily unavailable.
 */
class WorksheetSynchronizer
{
    public function __construct(private readonly GoogleSheetsService $sheets) {}

    /**
     * @throws GoogleSheetsException when Google Sheets cannot be reached.
     */
    public function sync(Section $section): void
    {
        $section->loadMissing('schoolClass');

        $target = $section->worksheetName();
        $previous = $section->worksheet;

        try {
            if ($previous !== null && $previous !== '' && $previous !== $target && $this->sheets->worksheetExists($previous)) {
                // Rename in place so existing student rows are preserved.
                $this->sheets->renameWorksheet($previous, $target);
            } else {
                // Create the worksheet if missing; a no-op when it already exists.
                $this->sheets->ensureWorksheet($target);
            }

            $section->markSynced($target);
        } catch (GoogleSheetsException $e) {
            $section->markSyncFailed($e->getMessage());

            throw $e;
        }
    }
}
