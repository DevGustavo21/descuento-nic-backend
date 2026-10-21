<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::query()
            ->with(['business.category', 'category'])
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now())
            ->whereColumn('redemptions_count', '<', 'max_redemptions')
            ->whereHas('business', fn ($q) => $q->where('status', 'approved'));

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($department = $request->string('department')->toString()) {
            $query->whereHas('business', fn ($q) => $q->where('department', $department));
        }

        if ($request->filled('min_discount')) {
            $query->where('discount_type', 'percent')
                ->where('discount_value', '>=', (int) $request->input('min_discount'));
        }

        if ($channel = $request->string('channel')->toString()) {
            $query->whereJsonContains('channels', $channel);
        }

        if ($payment = $request->string('payment')->toString()) {
            $query->whereJsonContains('payment_methods', $payment);
        }

        if ($request->string('vigencia')->toString() === 'new') {
            $query->where('created_at', '>=', now()->subDays(14));
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'discount' => $query->orderByDesc('discount_value'),
            'popular' => $query->orderByDesc('redemptions_count'),
            default => $query->latest(),
        };

        $perPage = min(48, max(1, (int) $request->integer('per_page', 24)));

        $coupons = $query->paginate($perPage)->through(fn (Coupon $coupon) => $this->serialize($coupon));

        return response()->json($coupons);
    }

    public function show(string $code): JsonResponse
    {
        $coupon = Coupon::query()
            ->with(['business.category', 'category'])
            ->where('code', $code)
            ->firstOrFail();

        return response()->json($this->serialize($coupon));
    }

    public function filters(): JsonResponse
    {
        return response()->json([
            'categories' => Category::query()->orderBy('sort_order')->get(['name', 'slug', 'icon']),
            'departments' => config('descuentonic.departments'),
            'channels' => config('descuentonic.channels'),
            'payment_methods' => config('descuentonic.payment_methods'),
        ]);
    }

    private function serialize(Coupon $coupon): array
    {
        $createdRecently = $coupon->created_at?->gte(now()->subDays(14));
        $expiringSoon = $coupon->expires_at->lte(now()->addDays(5));
        $popular = $coupon->redemptions_count >= 20;

        $badge = null;
        if ($expiringSoon) {
            $badge = 'expires';
        } elseif ($popular) {
            $badge = 'hot';
        } elseif ($createdRecently) {
            $badge = 'new';
        }

        return [
            'id' => $coupon->id,
            'title' => $coupon->title,
            'code' => $coupon->code,
            'store' => $coupon->business?->name,
            'logo_url' => $coupon->business?->logoUrl(),
            'department' => $coupon->business?->department,
            'city' => $coupon->business?->city ?: $coupon->business?->department,
            'category' => $coupon->category?->name ?? $coupon->business?->category?->name,
            'category_slug' => $coupon->category?->slug ?? $coupon->business?->category?->slug,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'discount_label' => $coupon->discountLabel(),
            'expires_at' => $coupon->expires_at->toDateString(),
            'channels' => $coupon->channels ?? [],
            'payment_methods' => $coupon->payment_methods ?? [],
            'terms' => $coupon->terms,
            'badge' => $badge,
            'remaining' => $coupon->remainingRedemptions(),
            'max_redemptions' => $coupon->max_redemptions,
        ];
    }
}
