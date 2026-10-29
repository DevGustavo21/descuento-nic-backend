<?php

namespace App\Filament\Negocio\Widgets;

use App\Models\Redemption;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentRedemptionsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $businessId = auth()->user()?->business_id;

        return $table
            ->heading('Canjes recientes')
            ->description('Últimos canjes registrados en tu negocio.')
            ->query(
                Redemption::query()
                    ->with('coupon')
                    ->where('business_id', $businessId)
                    ->latest('redeemed_at')
            )
            ->defaultPaginationPageOption(5)
            ->paginated([5])
            ->columns([
                TextColumn::make('redeemed_at')->label('Cuándo')->since(),
                TextColumn::make('coupon.code')->label('Cupón')->badge()->color('primary')->placeholder('—'),
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
            ->emptyStateHeading('Sin canjes todavía')
            ->emptyStateDescription('Cuando registres un canje aparecerá aquí.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck);
    }
}
