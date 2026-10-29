<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'kicker', 'title', 'text', 'cta_label', 'cta_url',
        'image_path', 'image_url', 'tone', 'sort_order',
        'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function resolvedImageUrl(): ?string
    {
        if ($this->image_url) {
            return $this->image_url;
        }

        if ($this->image_path) {
            return url(Storage::disk('public')->url($this->image_path));
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'kicker' => $this->kicker,
            'title' => $this->title,
            'text' => $this->text,
            'cta' => $this->cta_label,
            'href' => $this->cta_url,
            'image' => $this->resolvedImageUrl(),
            'tone' => $this->tone ?: 'from-rose-700/85 to-rose-900/70',
        ];
    }
}
