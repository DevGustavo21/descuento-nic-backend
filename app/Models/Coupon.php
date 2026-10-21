<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'business_id', 'category_id', 'title', 'code', 'discount_type',
        'discount_value', 'starts_at', 'expires_at', 'max_redemptions',
        'redemptions_count', 'channels', 'payment_methods', 'terms', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'channels' => 'array',
            'payment_methods' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Coupon $coupon): void {
            if (blank($coupon->code)) {
                $coupon->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(3)).rand(10, 99).Str::upper(Str::random(2));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function remainingRedemptions(): int
    {
        return max(0, $this->max_redemptions - $this->redemptions_count);
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at->endOfDay()->isPast()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return $this->remainingRedemptions() > 0;
    }

    public function discountLabel(): string
    {
        return match ($this->discount_type) {
            'amount' => 'C$'.$this->discount_value,
            default => $this->discount_value.'%',
        };
    }
}
