<?php

namespace App\Filament\Negocio\Widgets;

use App\Models\Coupon;
use App\Models\Redemption;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CouponStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $businessId = auth()->user()?->business_id;

        if (! $businessId) {
            return [];
        }

        $active = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now())
            ->count();

        $redemptions = Redemption::query()
            ->where('business_id', $businessId)
            ->count();

        $month = Redemption::query()
            ->where('business_id', $businessId)
            ->where('redeemed_at', '>=', now()->startOfMonth())
            ->count();

        $remaining = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->get()
            ->sum(fn (Coupon $coupon) => $coupon->remainingRedemptions());

        $expiring = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->count();

        return [
            Stat::make('Cupones activos', (string) $active),
            Stat::make('Canjes totales', (string) $redemptions),
            Stat::make('Canjes este mes', (string) $month),
            Stat::make('Canjes restantes', (string) $remaining),
            Stat::make('Vencen en 7 días', (string) $expiring),
        ];
    }
}
