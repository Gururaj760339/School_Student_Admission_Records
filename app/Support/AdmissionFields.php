<?php

namespace App\Support;

/**
 * Access helpers for the 30 configured admission fields.
 *
 * The field order defined in config/admission.php is authoritative: it drives
 * validation, form rendering and the Google Sheets header row.
 */
class AdmissionFields
{
    /**
     * Synthetic per-row identifier used internally for view/edit/delete.
     *
     * This is NOT a spreadsheet column: worksheets contain exactly the 30
     * student fields. The identifier is derived from the physical row number
     * when records are read, so no extra column is ever expected or written.
     */
    public const RECORD_ID = 'record_id';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return config('admission.fields', []);
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(static::all());
    }

    /**
     * The expected Google Sheets header row: exactly the 30 student fields,
     * in the configured order. There is no record_id column.
     *
     * This is the single source of truth for every configured class, so all
     * current and future worksheets are validated identically.
     *
     * @return list<string>
     */
    public static function headers(): array
    {
        return static::names();
    }

    /**
     * @return list<string>
     */
    public static function required(): array
    {
        return config('admission.required_fields', []);
    }

    public static function isRequired(string $field): bool
    {
        return in_array($field, static::required(), true);
    }

    /**
     * Validation rules keyed by field name.
     *
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        $rules = [];

        foreach (static::all() as $name => $field) {
            $rules[$name] = $field['rules'] ?? ['nullable', 'string'];
        }

        return $rules;
    }

    public static function label(string $field): string
    {
        return static::all()[$field]['label'] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (static::all() as $name => $field) {
            $labels[$name] = $field['label'] ?? $name;
        }

        return $labels;
    }

    /**
     * Blank payload containing the 30 fields as null.
     *
     * @return array<string, null>
     */
    public static function blank(): array
    {
        return array_fill_keys(static::headers(), null);
    }

    /**
     * Keep only known fields and cast every value to a trimmed string/null.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    public static function normalize(array $data): array
    {
        $normalized = [];

        foreach (static::names() as $name) {
            $value = $data[$name] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            $normalized[$name] = ($value === '' || $value === null) ? null : (string) $value;
        }

        return $normalized;
    }
}
