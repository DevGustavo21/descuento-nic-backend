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
        'department', 'city', 'address', 'description', 'category_id', 'status',
        'notes', 'rejection_reason', 'rejected_at', 'rejected_by', 'approved_at',
        'approved_by', 'plan', 'is_featured', 'featured_until',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'featured_until' => 'datetime',
            'is_featured' => 'boolean',
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

    public function branches(): HasMany
    {
        return $this->hasMany(BusinessBranch::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFeaturedNow(): bool
    {
        if (! $this->is_featured && $this->plan !== 'featured') {
            return false;
        }

        if ($this->featured_until && $this->featured_until->isPast()) {
            return false;
        }

        return true;
    }

    public function logoUrl(): string
    {
        if (is_string($this->logo_path) && str_starts_with($this->logo_path, 'data:')) {
            return $this->logo_path;
        }

        // Always go through a mime-aware route so WebP/JPEG/PNG/SVG render correctly.
        $version = $this->updated_at?->timestamp ?? $this->id;

        return url('/media/businesses/'.$this->id.'/logo').'?v='.$version;
    }
}
