<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\Redemption;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Resumen operativo';

    protected function getStats(): array
    {
        $pending = Business::query()->where('status', 'pending')->count();
        $activeCoupons = Coupon::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now())
            ->whereNull('force_expired_at')
            ->count();
        $today = Redemption::query()->whereDate('redeemed_at', today())->count();
        $month = Redemption::query()->where('redeemed_at', '>=', now()->startOfMonth())->count();
        $newBusinesses = Business::query()->where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('Negocios pendientes', (string) $pending)
                ->description('Esperan aprobación')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($pending > 0 ? 'warning' : 'success')
                ->url('/admin/businesses?tableFilters[status][value]=pending'),
            Stat::make('Cupones activos', (string) $activeCoupons)
                ->description('Publicados y vigentes')
                ->descriptionIcon(Heroicon::OutlinedTicket)
                ->color('primary'),
            Stat::make('Canjes hoy', (string) $today)
                ->description($month.' este mes')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color('success'),
            Stat::make('Negocios nuevos (30d)', (string) $newBusinesses)
                ->description('Altas recientes')
                ->descriptionIcon(Heroicon::OutlinedBuildingStorefront)
                ->color('info'),
        ];
    }
}
