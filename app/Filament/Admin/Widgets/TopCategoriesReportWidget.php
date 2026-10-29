<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Category;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopCategoriesReportWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Categorías con más cupones';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        $max = max(1, (int) Category::query()->withCount('coupons')->get()->max('coupons_count'));

        return $table
            ->heading('Categorías con más cupones')
            ->description('Distribución del catálogo por rubro.')
            ->query(
                Category::query()
                    ->withCount(['coupons', 'businesses'])
                    ->orderByDesc('coupons_count')
            )
            ->defaultPaginationPageOption(10)
            ->paginated([10])
            ->columns([
                TextColumn::make('name')
                    ->label('Categoría')
                    ->description(fn (Category $record) => $record->businesses_count.' negocios'),
                TextColumn::make('coupons_count')
                    ->label('Cupones')
                    ->alignEnd()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('share')
                    ->label('Participación')
                    ->state(fn (Category $record) => $max > 0
                        ? (int) round(($record->coupons_count / $max) * 100)
                        : 0)
                    ->formatStateUsing(function (int $state): string {
                        $width = max(8, $state);

                        return '<div style="display:flex;align-items:center;gap:10px;min-width:140px">'
                            .'<div style="flex:1;height:8px;border-radius:999px;background:rgba(148,163,184,.25);overflow:hidden">'
                            .'<div style="height:100%;width:'.$width.'%;border-radius:999px;background:#e11d48"></div>'
                            .'</div>'
                            .'<span style="font-size:12px;font-weight:600;color:#64748b;width:36px;text-align:right">'.$state.'%</span>'
                            .'</div>';
                    })
                    ->html(),
            ])
            ->emptyStateHeading('Sin categorías')
            ->emptyStateDescription('Creá categorías para organizar el catálogo.')
            ->emptyStateIcon(Heroicon::OutlinedTag);
    }
}
