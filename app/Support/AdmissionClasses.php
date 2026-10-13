<?php

namespace App\Support;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Single source of truth for the selectable admission targets.
 *
 * Targets are Class + Section pairs managed dynamically in the database from
 * the admin panel. Each section maps to its own worksheet named
 * "<Class> - <Section>" (for example "Class 1 - A").
 *
 * Users only ever submit a server-controlled section slug, which is validated
 * against the database; worksheet names are never taken directly from user
 * input.
 *
 * Legacy compatibility: on an installation that has not yet created any
 * database class, the small set of classes previously declared in
 * config/google_sheets.php is used as a fallback so existing deployments keep
 * working until they are migrated to the dynamic classes. As soon as at least
 * one class exists in the database, the database is authoritative.
 */
class AdmissionClasses
{
    /**
     * @return array<string, array{name: string, worksheet: string}>
     */
    public static function all(): array
    {
        return static::fromDatabase() ?? config('google_sheets.classes', []);
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(static::all());
    }

    public static function exists(?string $slug): bool
    {
        return $slug !== null && array_key_exists($slug, static::all());
    }

    /**
     * @return array{name: string, worksheet: string}|null
     */
    public static function find(?string $slug): ?array
    {
        if (! static::exists($slug)) {
            return null;
        }

        return static::all()[$slug];
    }

    public static function worksheetFor(?string $slug): ?string
    {
        return static::find($slug)['worksheet'] ?? null;
    }

    public static function nameFor(?string $slug): ?string
    {
        return static::find($slug)['name'] ?? null;
    }

    public static function classNameFor(?string $slug): ?string
    {
        return static::find($slug)['class_name'] ?? static::nameFor($slug);
    }

    public static function sectionNameFor(?string $slug): ?string
    {
        return static::find($slug)['section_name'] ?? null;
    }

    /**
     * Resolve the display name of an admission target by its worksheet name.
     */
    public static function nameForWorksheet(?string $worksheet): ?string
    {
        foreach (static::all() as $class) {
            if (($class['worksheet'] ?? null) === $worksheet) {
                return $class['name'];
            }
        }

        return null;
    }

    /**
     * The preferred default target slug, falling back to the first available.
     */
    public static function defaultSlug(): ?string
    {
        $defaultName = (string) config('google_sheets.default_class');

        foreach (static::all() as $slug => $class) {
            $name = (string) ($class['name'] ?? '');

            if (Str::lower($name) === Str::lower($defaultName)
                || Str::startsWith(Str::lower($name), Str::lower($defaultName).' - ')) {
                return (string) $slug;
            }
        }

        return static::slugs()[0] ?? null;
    }

    /**
     * A map of option slug => display name, suitable for <select> inputs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (static::all() as $slug => $class) {
            $options[$slug] = $class['name'];
        }

        return $options;
    }

    /**
     * Classes grouped with their sections, for the public admission page.
     *
     * @return list<array{name: string, slug: string, sections: list<array{option_slug: string, name: string|null, worksheet: string}>}>
     */
    public static function grouped(): array
    {
        if (static::databaseHasClasses()) {
            $groups = [];

            foreach (static::databaseClasses() as $class) {
                $sections = [];

                foreach ($class->sections->sortBy('name') as $section) {
                    $sections[] = [
                        'option_slug' => $section->slug,
                        'name' => $section->name,
                        'worksheet' => $class->worksheetNameFor($section->name),
                    ];
                }

                $groups[] = [
                    'name' => $class->name,
                    'slug' => $class->slug,
                    'sections' => $sections,
                ];
            }

            return $groups;
        }

        // Legacy fallback: each configured class is a single target.
        $groups = [];

        foreach (config('google_sheets.classes', []) as $slug => $class) {
            $groups[] = [
                'name' => $class['name'] ?? $slug,
                'slug' => (string) $slug,
                'sections' => [[
                    'option_slug' => (string) $slug,
                    'name' => null,
                    'worksheet' => $class['worksheet'] ?? (string) $slug,
                ]],
            ];
        }

        return $groups;
    }

    public static function usesDatabase(): bool
    {
        return static::databaseHasClasses();
    }

    /**
     * @return array<string, array{name: string, worksheet: string, class_name: string, section_name: string}>|null
     */
    private static function fromDatabase(): ?array
    {
        if (! static::databaseHasClasses()) {
            return null;
        }

        $options = [];

        foreach (static::databaseClasses() as $class) {
            foreach ($class->sections->sortBy('name') as $section) {
                $options[$section->slug] = [
                    'name' => $class->name.' - '.$section->name,
                    'worksheet' => $class->worksheetNameFor($section->name),
                    'class_name' => $class->name,
                    'section_name' => $section->name,
                ];
            }
        }

        return $options;
    }

    private static function databaseHasClasses(): bool
    {
        if (! static::databaseReady()) {
            return false;
        }

        try {
            return SchoolClass::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private static function databaseClasses()
    {
        return SchoolClass::query()->with('sections')->orderBy('name')->get();
    }

    private static function databaseReady(): bool
    {
        try {
            return Schema::hasTable('school_classes') && Schema::hasTable('sections');
        } catch (Throwable) {
            return false;
        }
    }
}
