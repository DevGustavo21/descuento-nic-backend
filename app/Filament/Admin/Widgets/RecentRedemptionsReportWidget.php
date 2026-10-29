<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Redemption;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentRedemptionsReportWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Canjes recientes';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Canjes recientes')
            ->description('Últimos movimientos del mes.')
            ->query(
                Redemption::query()
                    ->with(['coupon', 'business'])
                    ->where('redeemed_at', '>=', now()->startOfMonth())
                    ->latest('redeemed_at')
            )
            ->defaultPaginationPageOption(8)
            ->paginated([8])
            ->columns([
                TextColumn::make('redeemed_at')
                    ->label('Cuándo')
                    ->since()
                    ->sortable(false),
                TextColumn::make('coupon.code')
                    ->label('Cupón')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('business.name')
                    ->label('Negocio')
                    ->limit(24)
                    ->placeholder('—'),
                TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_store' => 'Tienda',
                        'online' => 'Online',
                        default => $state,
                    })
                    ->color('success'),
            ])
            ->emptyStateHeading('Sin canjes este mes')
            ->emptyStateDescription('Los canjes del mes aparecerán aquí en tiempo real.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck);
    }
}
