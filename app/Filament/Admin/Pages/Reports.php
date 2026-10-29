<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\RecentRedemptionsReportWidget;
use App\Filament\Admin\Widgets\ReportsStatsOverview;
use App\Filament\Admin\Widgets\TopBusinessesReportWidget;
use App\Filament\Admin\Widgets\TopCategoriesReportWidget;
use App\Models\Business;
use App\Models\Redemption;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes';

    protected ?string $subheading = 'Rendimiento de negocios, categorías y canjes.';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.reports';

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ReportsStatsOverview::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            TopBusinessesReportWidget::class,
            TopCategoriesReportWidget::class,
            RecentRedemptionsReportWidget::class,
        ];
    }

    /**
     * @return int | array<string, int | null>
     */
    public function getFooterWidgetsColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportBusinesses')
                ->label('Exportar negocios')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => $this->exportBusinessesCsv()),
            Action::make('exportRedemptions')
                ->label('Exportar canjes del mes')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('primary')
                ->action(fn () => $this->exportMonthRedemptionsCsv()),
        ];
    }

    protected function exportBusinessesCsv(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['negocio', 'departamento', 'estado', 'plan', 'canjes', 'cupones']);

            Business::query()
                ->withCount(['redemptions', 'coupons'])
                ->orderByDesc('redemptions_count')
                ->chunk(100, function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->name,
                            $row->department,
                            $row->status,
                            $row->plan,
                            $row->redemptions_count,
                            $row->coupons_count,
                        ]);
                    }
                });

            fclose($handle);
        }, 'reporte-negocios-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function exportMonthRedemptionsCsv(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['fecha', 'codigo', 'negocio', 'fuente']);

            Redemption::query()
                ->with(['coupon', 'business'])
                ->where('redeemed_at', '>=', now()->startOfMonth())
                ->latest('redeemed_at')
                ->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            optional($row->redeemed_at)?->toDateTimeString(),
                            $row->coupon?->code,
                            $row->business?->name,
                            $row->source,
                        ]);
                    }
                });

            fclose($handle);
        }, 'canjes-mes-'.now()->format('Y-m').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
