<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteConfig
{
    public const KEYS = [
        'departments',
        'channels',
        'payment_methods',
        'free_email_domains',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'departments' => self::get('departments', config('descuentonic.departments', [])),
            'channels' => self::get('channels', config('descuentonic.channels', [])),
            'payment_methods' => self::get('payment_methods', config('descuentonic.payment_methods', [])),
            'free_email_domains' => self::get('free_email_domains', config('descuentonic.free_email_domains', [])),
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting.{$key}", 300, function () use ($key, $default) {
            $row = SiteSetting::query()->find($key);

            return $row?->value ?? $default;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function save(array $data): void
    {
        foreach (self::KEYS as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if ($key === 'departments') {
                $catalog = config('descuentonic.departments', []);
                $selected = is_string($value)
                    ? collect(preg_split('/[\n,]+/', $value) ?: [])
                    : collect((array) $value);

                $value = $selected
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->unique()
                    ->sortBy(fn (string $department) => array_search($department, $catalog, true))
                    ->values()
                    ->all();
            }

            if ($key === 'free_email_domains' && is_string($value)) {
                $value = collect(preg_split('/[\n,]+/', $value) ?: [])
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->values()
                    ->all();
            }

            if (in_array($key, ['channels', 'payment_methods'], true) && is_array($value)) {
                $normalized = [];
                foreach ($value as $k => $v) {
                    if (is_array($v) && isset($v['key'], $v['label'])) {
                        $normalized[$v['key']] = $v['label'];
                    } elseif (is_string($k)) {
                        $normalized[$k] = $v;
                    }
                }
                $value = $normalized;
            }

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );

            Cache::forget("site_setting.{$key}");
        }
    }

    /**
     * @return list<string>
     */
    public static function departments(): array
    {
        $value = self::get('departments', config('descuentonic.departments', []));

        return array_values(array_filter(array_map('strval', (array) $value)));
    }

    /**
     * @return array<string, string>
     */
    public static function channels(): array
    {
        return (array) self::get('channels', config('descuentonic.channels', []));
    }

    /**
     * @return array<string, string>
     */
    public static function paymentMethods(): array
    {
        return (array) self::get('payment_methods', config('descuentonic.payment_methods', []));
    }

    /**
     * @return list<string>
     */
    public static function freeEmailDomains(): array
    {
        $value = self::get('free_email_domains', config('descuentonic.free_email_domains', []));

        return array_values(array_filter(array_map('strval', (array) $value)));
    }
}
