<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BusinessLogo
{
    /**
     * @var list<array{0: string, 1: string}>
     */
    private const PALETTES = [
        ['#E11D48', '#9F1239'],
        ['#2563EB', '#1E40AF'],
        ['#0F766E', '#115E59'],
        ['#C2410C', '#9A3412'],
        ['#7C3AED', '#5B21B6'],
        ['#B45309', '#92400E'],
        ['#0369A1', '#075985'],
        ['#BE123C', '#9F1239'],
        ['#15803D', '#166534'],
        ['#1D4ED8', '#1E3A8A'],
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
        [$from, $to] = self::PALETTES[abs(crc32($slug)) % count(self::PALETTES)];
        $initials = htmlspecialchars(self::initials($name), ENT_XML1);
        $label = htmlspecialchars($name, ENT_XML1);
        $gradId = 'g'.substr(md5($slug), 0, 8);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="{$label}">
  <defs>
    <linearGradient id="{$gradId}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$from}"/>
      <stop offset="100%" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="96" height="96" rx="22" fill="url(#{$gradId})"/>
  <circle cx="72" cy="22" r="28" fill="rgba(255,255,255,0.12)"/>
  <circle cx="18" cy="78" r="20" fill="rgba(255,255,255,0.10)"/>
  <rect x="18" y="18" width="60" height="60" rx="18" fill="rgba(255,255,255,0.16)"/>
  <text x="48" y="58" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#ffffff">{$initials}</text>
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
