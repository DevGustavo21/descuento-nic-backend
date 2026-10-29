<?php

namespace App\Filament\Negocio\Widgets;

use App\Models\Coupon;
use App\Models\Redemption;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CouponStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Resumen de cupones';

    protected ?string $description = 'Actividad de tu negocio en DescuentoNic.';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $businessId = auth()->user()?->business_id;

        if (! $businessId) {
            return [];
        }

        $active = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->whereNull('force_expired_at')
            ->whereDate('expires_at', '>=', now())
            ->count();

        $redemptions = Redemption::query()
            ->where('business_id', $businessId)
            ->count();

        $month = Redemption::query()
            ->where('business_id', $businessId)
            ->where('redeemed_at', '>=', now()->startOfMonth())
            ->count();

        $prevMonth = Redemption::query()
            ->where('business_id', $businessId)
            ->whereBetween('redeemed_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();

        $delta = $month - $prevMonth;

        $remaining = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->get()
            ->sum(fn (Coupon $coupon) => $coupon->remainingRedemptions());

        $expiring = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->whereNull('force_expired_at')
            ->whereBetween('expires_at', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        return [
            Stat::make('Cupones activos', (string) $active)
                ->description('Publicados y vigentes')
                ->descriptionIcon(Heroicon::OutlinedTicket)
                ->icon(Heroicon::OutlinedTicket)
                ->color('primary')
                ->url('/negocio/coupons?tableFilters[status][value]=active'),
            Stat::make('Canjes este mes', (string) $month)
                ->description($delta >= 0 ? '+'.$delta.' vs mes anterior' : $delta.' vs mes anterior')
                ->descriptionIcon($delta >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color($delta >= 0 ? 'success' : 'danger')
                ->url('/negocio/redemptions'),
            Stat::make('Canjes totales', (string) $redemptions)
                ->description($remaining.' canjes restantes')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->icon(Heroicon::OutlinedChartBar)
                ->color('info'),
            Stat::make('Por vencer', (string) $expiring)
                ->description('Caducan en los próximos 7 días')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedClock)
                ->color($expiring > 0 ? 'warning' : 'gray'),
        ];
    }
}
