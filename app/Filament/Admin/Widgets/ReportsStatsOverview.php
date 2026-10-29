<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\Redemption;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportsStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Indicadores clave';

    protected ?string $description = 'Actividad de la plataforma en el período actual.';

    protected function getStats(): array
    {
        $monthRedemptions = Redemption::query()
            ->where('redeemed_at', '>=', now()->startOfMonth())
            ->count();

        $prevMonthRedemptions = Redemption::query()
            ->whereBetween('redeemed_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();

        $delta = $monthRedemptions - $prevMonthRedemptions;
        $deltaLabel = $prevMonthRedemptions === 0
            ? ($monthRedemptions > 0 ? 'Nuevo este mes' : 'Sin canjes previos')
            : (($delta >= 0 ? '+' : '').$delta.' vs mes anterior');

        $activeCoupons = Coupon::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now())
            ->whereNull('force_expired_at')
            ->count();

        $approvedBusinesses = Business::query()->where('status', 'approved')->count();
        $pendingBusinesses = Business::query()->where('status', 'pending')->count();
        $featuredBusinesses = Business::query()
            ->where(function ($q): void {
                $q->where('is_featured', true)->orWhere('plan', 'featured');
            })
            ->where(function ($q): void {
                $q->whereNull('featured_until')->orWhere('featured_until', '>=', now());
            })
            ->count();

        $merchants = User::query()->where('role', 'merchant')->where('is_active', true)->count();

        return [
            Stat::make('Canjes este mes', number_format($monthRedemptions))
                ->description($deltaLabel)
                ->descriptionIcon($delta >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($delta >= 0 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->url('/admin/redemptions'),
            Stat::make('Cupones activos', number_format($activeCoupons))
                ->description('Vigentes en catálogo')
                ->descriptionIcon(Heroicon::OutlinedTicket)
                ->color('primary')
                ->icon(Heroicon::OutlinedTicket)
                ->url('/admin/coupons'),
            Stat::make('Negocios aprobados', number_format($approvedBusinesses))
                ->description($pendingBusinesses.' pendientes de revisión')
                ->descriptionIcon(Heroicon::OutlinedBuildingStorefront)
                ->color('info')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->url('/admin/businesses'),
            Stat::make('Planes destacados', number_format($featuredBusinesses))
                ->description($merchants.' comercios activos')
                ->descriptionIcon(Heroicon::OutlinedStar)
                ->color('warning')
                ->icon(Heroicon::OutlinedStar),
        ];
    }
}
