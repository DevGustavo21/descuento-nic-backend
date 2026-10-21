<?php

use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;

Route::get('/filters', [CouponController::class, 'filters']);
Route::get('/coupons', [CouponController::class, 'index']);
Route::get('/coupons/{code}', [CouponController::class, 'show']);
