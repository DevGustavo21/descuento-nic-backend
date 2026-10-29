<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Businesses\BusinessResource;
use App\Models\Business;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopBusinessesReportWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Top negocios por canjes';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top negocios por canjes')
            ->description('Los 10 comercios con más canjes registrados.')
            ->query(
                Business::query()
                    ->with('category')
                    ->withCount(['redemptions', 'coupons'])
                    ->orderByDesc('redemptions_count')
            )
            ->defaultPaginationPageOption(10)
            ->paginated([10])
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->badge()
                    ->color(fn ($state): string => match ((int) $state) {
                        1 => 'warning',
                        2 => 'gray',
                        3 => 'danger',
                        default => 'gray',
                    }),
                ImageColumn::make('logo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Business $record) => $record->logoUrl())
                    ->getStateUsing(fn (Business $record) => $record->logo_path ?: $record->logoUrl()),
                TextColumn::make('name')
                    ->label('Negocio')
                    ->description(fn (Business $record) => $record->category?->name)
                    ->searchable(false),
                TextColumn::make('department')->label('Depto.')->toggleable(),
                TextColumn::make('plan')
                    ->label('Plan')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'featured' ? 'warning' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'featured' ? 'Destacado' : 'Gratis'),
                TextColumn::make('coupons_count')
                    ->label('Cupones')
                    ->alignEnd(),
                TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->alignEnd()
                    ->sortable(false)
                    ->color('primary')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Ver')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Business $record) => BusinessResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Sin canjes todavía')
            ->emptyStateDescription('Cuando los comercios registren canjes, el ranking aparecerá aquí.')
            ->emptyStateIcon(Heroicon::OutlinedChartBar);
    }
}
