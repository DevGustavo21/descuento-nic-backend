<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class StoredUpload
{
    public static function extensionFor(TemporaryUploadedFile|UploadedFile|string $file): string
    {
        $mime = null;

        if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile) {
            $mime = $file->getMimeType();
        } elseif (is_string($file) && is_file($file)) {
            $mime = mime_content_type($file) ?: null;
        }

        return match ($mime) {
            'image/webp' => 'webp',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };
    }

    public static function storePublicImage(TemporaryUploadedFile $file, string $directory): string
    {
        $filename = Str::ulid().'.'.self::extensionFor($file);

        return $file->storeAs($directory, $filename, 'public');
    }

    public static function ensureExtensionMatchesMime(?string $relativePath): ?string
    {
        if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        $absolute = Storage::disk('public')->path($relativePath);
        $mime = mime_content_type($absolute) ?: null;
        $expected = match ($mime) {
            'image/webp' => 'webp',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/jpeg' => 'jpg',
            default => null,
        };

        if (! $expected) {
            return $relativePath;
        }

        $current = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($current === $expected || ($current === 'jpeg' && $expected === 'jpg')) {
            return $relativePath;
        }

        $newRelative = preg_replace('/\.[^.]+$/', '.'.$expected, $relativePath) ?: $relativePath.'.'.$expected;
        Storage::disk('public')->move($relativePath, $newRelative);

        return $newRelative;
    }
}
