<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }
}
