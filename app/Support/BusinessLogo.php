<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BusinessLogo
{
    /**
     * @var list<string>
     */
    private const COLORS = [
        '#E11D48', '#2563EB', '#0F766E', '#C2410C', '#7C3AED',
        '#B45309', '#0369A1', '#BE123C', '#15803D', '#1D4ED8',
    ];

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        $letters = collect($words)
            ->filter(fn (string $word) => $word !== '')
            ->take(2)
            ->map(function (string $word) {
                $clean = preg_replace('/[^\p{L}]/u', '', $word) ?? $word;

                return Str::upper(Str::substr($clean !== '' ? $clean : $word, 0, 1));
            })
            ->implode('');

        return $letters !== '' ? $letters : 'DN';
    }

    public static function svgMarkup(string $name, string $slug): string
    {
        $bg = self::COLORS[abs(crc32($slug)) % count(self::COLORS)];
        $initials = htmlspecialchars(self::initials($name), ENT_XML1);
        $label = htmlspecialchars($name, ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="{$label}">
  <rect width="96" height="96" rx="22" fill="{$bg}"/>
  <text x="48" y="58" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="32" font-weight="700" fill="#ffffff">{$initials}</text>
</svg>
SVG;
    }

    public static function dataUri(string $name, string $slug): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svgMarkup($name, $slug));
    }

    public static function writeSvg(string $name, string $slug): string
    {
        $dir = public_path('logos');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/'.$slug.'.svg', self::svgMarkup($name, $slug));

        $frontendDir = dirname(base_path()).'/web/public/logos';
        if (is_dir(dirname($frontendDir))) {
            File::ensureDirectoryExists($frontendDir);
            File::put($frontendDir.'/'.$slug.'.svg', self::svgMarkup($name, $slug));
        }

        return self::dataUri($name, $slug);
    }
}
