<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->currentlyVisible()
            ->get()
            ->map(fn (Banner $banner) => $banner->toPublicArray())
            ->values();

        return response()->json(['data' => $banners]);
    }
}
