<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A dynamically managed class (e.g. "Class 1").
 *
 * Classes are stored in the database and managed from the admin panel. Each
 * class owns one or more sections, and each section maps to its own Google
 * Sheets worksheet.
 */
class SchoolClass extends Model
{
    protected $table = 'school_classes';

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @return HasMany<Section, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'school_class_id');
    }

    /**
     * The display name of a worksheet for a section of this class.
     */
    public function worksheetNameFor(string $sectionName): string
    {
        return trim($this->name).' - '.trim($sectionName);
    }

    /**
     * Build a URL-safe, globally unique slug for a class name.
     */
    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'class';
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
}
