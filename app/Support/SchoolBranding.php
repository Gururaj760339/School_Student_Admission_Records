<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Single source of truth for the installation-wide school logo.
 *
 * The uploaded logo is kept on the private "local" disk (outside the web
 * root) and is only exposed to the public admission form through the
 * branding.logo.image route, so uploaded files are never listed or served
 * directly from the filesystem. The reference to the current logo is stored
 * alongside it so no database schema change is required.
 */
class SchoolBranding
{
    public const DISK = 'local';

    private const DIRECTORY = 'branding';

    private const META_PATH = 'branding/meta.json';

    /**
     * The disk-relative path of the stored logo, or null when none exists.
     */
    public static function logoPath(): ?string
    {
        return static::resolve(static::meta());
    }

    public static function hasLogo(): bool
    {
        return static::logoPath() !== null;
    }

    /**
     * Public URL of the stored logo, or null when none has been uploaded.
     */
    public static function url(): ?string
    {
        $meta = static::meta();

        if (static::resolve($meta) === null) {
            return null;
        }

        $version = $meta['updated_at'] ?? null;

        return $version === null
            ? route('branding.logo.image')
            : route('branding.logo.image', ['v' => $version]);
    }

    /**
     * Replace the current logo with the uploaded file.
     */
    public static function store(UploadedFile $file): string
    {
        $previous = static::logoPath();

        if ($previous !== null) {
            Storage::disk(static::DISK)->delete($previous);
        }

        $name = 'logo-'.$file->hashName();
        $path = static::DIRECTORY.'/'.$name;

        Storage::disk(static::DISK)->putFileAs(static::DIRECTORY, $file, $name);

        Storage::disk(static::DISK)->put(static::META_PATH, json_encode([
            'logo' => $path,
            'updated_at' => now()->getTimestamp(),
        ]));

        return $path;
    }

    /**
     * @return array{logo?: string, updated_at?: int}
     */
    private static function meta(): array
    {
        $disk = Storage::disk(static::DISK);

        if (! $disk->exists(static::META_PATH)) {
            return [];
        }

        $decoded = json_decode((string) $disk->get(static::META_PATH), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function resolve(array $meta): ?string
    {
        $path = $meta['logo'] ?? null;

        if (! is_string($path) || $path === '' || ! Storage::disk(static::DISK)->exists($path)) {
            return null;
        }

        return $path;
    }
}
