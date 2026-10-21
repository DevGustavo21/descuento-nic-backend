<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    protected $fillable = [
        'coupon_id', 'business_id', 'source', 'notes', 'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
        ];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
