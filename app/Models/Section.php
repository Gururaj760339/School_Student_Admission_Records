<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A section within a class (e.g. "A" in "Class 1"). Each section is the unit
 * of admission: it maps to exactly one Google Sheets worksheet named
 * "<Class> - <Section>".
 */
class Section extends Model
{
    public const SYNC_PENDING = 'pending';

    public const SYNC_SYNCED = 'synced';

    public const SYNC_FAILED = 'failed';

    protected $table = 'sections';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_class_id',
        'name',
        'slug',
        'worksheet',
        'google_sync_status',
        'google_sync_error',
        'google_synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'google_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * The worksheet (tab) name this section must be stored in.
     */
    public function worksheetName(): string
    {
        $this->loadMissing('schoolClass');

        return $this->schoolClass->worksheetNameFor($this->name);
    }

    /**
     * Build a URL-safe, globally unique slug for a section of a class.
     */
    public static function generateSlug(SchoolClass $class, string $sectionName, ?int $ignoreId = null): string
    {
        $base = trim($class->slug.'-'.Str::slug($sectionName), '-');

        if ($base === '') {
            $base = 'section';
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function markSynced(string $worksheet): void
    {
        $this->forceFill([
            'worksheet' => $worksheet,
            'google_sync_status' => self::SYNC_SYNCED,
            'google_sync_error' => null,
            'google_synced_at' => now(),
        ])->save();
    }

    public function markSyncFailed(string $message): void
    {
        $this->forceFill([
            'google_sync_status' => self::SYNC_FAILED,
            'google_sync_error' => $message,
        ])->save();
    }

    public function isSynced(): bool
    {
        return $this->google_sync_status === self::SYNC_SYNCED;
    }
}
