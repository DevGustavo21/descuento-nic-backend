<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;

Route::get('/filters', [CouponController::class, 'filters']);
Route::get('/coupons', [CouponController::class, 'index']);
Route::get('/coupons/{code}/qr', [CouponController::class, 'qr']);
Route::get('/coupons/{code}', [CouponController::class, 'show']);
Route::get('/banners', [BannerController::class, 'index']);
