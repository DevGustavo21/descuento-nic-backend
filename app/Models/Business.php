<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo_path', 'website', 'email_domain', 'phone', 'ruc',
        'department', 'city', 'category_id', 'status', 'notes', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business): void {
            if (blank($business->slug)) {
                $business->slug = Str::slug($business->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function logoUrl(): string
    {
        if (is_string($this->logo_path) && str_starts_with($this->logo_path, 'data:')) {
            return $this->logo_path;
        }

        if ($this->logo_path) {
            return url('/storage/'.$this->logo_path);
        }

        if ($this->slug) {
            return \App\Support\BusinessLogo::dataUri($this->name, $this->slug);
        }

        return \App\Support\BusinessLogo::dataUri($this->name ?: 'DN', 'descuento-nic');
    }
}
