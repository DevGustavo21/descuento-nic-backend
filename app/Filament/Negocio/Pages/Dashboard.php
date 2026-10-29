<?php

namespace App\Filament\Negocio\Pages;

use App\Filament\Negocio\Widgets\BusinessStatusWidget;
use App\Filament\Negocio\Widgets\CouponStatsWidget;
use App\Filament\Negocio\Widgets\ExpiringCouponsWidget;
use App\Filament\Negocio\Widgets\RecentRedemptionsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';

    protected static ?string $title = 'Inicio';

    protected ?string $heading = 'Panel del negocio';

    protected ?string $subheading = 'Seguí tus cupones, canjes y el estado de tu cuenta.';

    public function getWidgets(): array
    {
        return [
            BusinessStatusWidget::class,
            CouponStatsWidget::class,
            ExpiringCouponsWidget::class,
            RecentRedemptionsWidget::class,
            AccountWidget::class,
        ];
    }

    /**
     * @return int | array<string, int | null>
     */
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
