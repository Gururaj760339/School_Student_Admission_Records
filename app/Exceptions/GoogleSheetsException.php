<?php

namespace App\Exceptions;

use App\Support\AdmissionFields;
use RuntimeException;
use Throwable;

/**
 * A safe, user-presentable error raised for any Google Sheets problem.
 *
 * The message is guaranteed not to contain the service-account private key,
 * the credentials file contents, or any student data. When wrapping a Google
 * API exception, the upstream message is scrubbed (and the previous exception
 * is preserved for local debugging) before it is exposed.
 */
class GoogleSheetsException extends RuntimeException
{
    private bool $isSetupError = false;

    public static function notConfigured(): self
    {
        $exception = new self(
            'Google Sheets is not configured. Set GOOGLE_SHEETS_SPREADSHEET_ID in the .env file.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function credentialsMissing(string $resolvedPath): self
    {
        $exception = new self(
            'Google service-account credentials were not found at the configured path. '
            .'Place the service-account JSON file at "'.$resolvedPath.'" (outside the public directory) '
            .'and make sure it is readable by the web server.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function credentialsUnreadable(string $resolvedPath): self
    {
        $exception = new self(
            'The Google service-account credentials file exists but is not readable at "'.$resolvedPath.'". '
            .'Check the file permissions.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function credentialsInvalid(): self
    {
        $exception = new self(
            'The Google service-account credentials are invalid or incomplete. '
            .'Download a fresh service-account key from Google Cloud and replace the local JSON file.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function authenticationFailed(Throwable $previous): self
    {
        $exception = new self(
            'Google authentication failed. The service-account key may be revoked, expired, or the system clock may be wrong. '
            .'Revoke the old key, create a replacement, and update the local credentials file.',
            previous: $previous
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function spreadsheetUnavailable(Throwable $previous): self
    {
        $exception = new self(
            'The configured Google Spreadsheet could not be opened. Confirm the Spreadsheet ID is correct and that the '
            .'service account has at least Editor access to the spreadsheet.',
            previous: $previous
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function worksheetMissing(string $worksheet): self
    {
        $exception = new self(
            'The worksheet tab "'.$worksheet.'" does not exist in the configured spreadsheet. '
            .'Create the tab manually and add the correct header row. The application will not create or overwrite it automatically.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function headersMismatch(string $worksheet, array $expected, array $actual): self
    {
        $exception = new self(
            'The header row of worksheet "'.$worksheet.'" does not match the expected columns. '
            .'Expected '.count($expected).' columns but found '.count($actual).'. '
            .'Fix the headers manually; existing data will not be overwritten.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function headersMissing(string $worksheet): self
    {
        $exception = new self(
            'Worksheet "'.$worksheet.'" has no header row. Add the '.count(AdmissionFields::headers())
            .' student column headers manually before submitting records. '
            .'The application will not create or overwrite worksheet headers automatically.'
        );
        $exception->isSetupError = true;

        return $exception;
    }

    public static function recordNotFound(string $recordId): self
    {
        return new self('The requested student record could not be found. It may have been deleted; refresh and try again.');
    }

    public static function api(string $operation, Throwable $previous): self
    {
        $status = trim((string) $previous->getCode());
        $detail = self::sanitize($previous->getMessage());

        $message = 'Google Sheets API error while '.$operation.'.';

        if ($detail !== '') {
            $message .= ' Detail: '.$detail;
        }

        if ($status !== '' && $status !== '0') {
            $message .= ' (code '.$status.')';
        }

        return new self($message, previous: $previous);
    }

    public function isSetupError(): bool
    {
        return $this->isSetupError;
    }

    /**
     * Remove anything that could resemble a credential or key from a message.
     */
    private static function sanitize(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        // Scrub anything that looks like a PEM private key block.
        $message = preg_replace('/-----BEGIN[^-]+-----.*?-----END[^-]+-----/is', '[redacted key]', $message) ?? $message;

        // Scrub long base64-ish blobs and "private_key" values.
        $message = preg_replace('/(private_key|privateKey)["\'\s:=]+[A-Za-z0-9_\-\/+=]{16,}/i', '$1: [redacted]', $message) ?? $message;

        if (mb_strlen($message) > 300) {
            $message = mb_substr($message, 0, 300).'…';
        }

        return $message;
    }
}
