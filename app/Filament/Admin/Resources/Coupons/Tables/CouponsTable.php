<?php

namespace App\Filament\Admin\Resources\Coupons\Tables;

use App\Models\Coupon;
use App\Support\Audit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->copyable()->sortable(),
                TextColumn::make('title')->label('Título')->searchable()->limit(40),
                TextColumn::make('business.name')->label('Negocio')->searchable()->sortable(),
                TextColumn::make('discount_value')
                    ->label('Descuento')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->discountLabel()),
                TextColumn::make('expires_at')
                    ->label('Vence')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Coupon $record): string => $record->expires_at->lte(now()->addDays(5)) ? 'warning' : 'gray'),
                TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->formatStateUsing(fn ($state, Coupon $record) => $state.'/'.$record->max_redemptions),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'paused' => 'Pausado',
                        'expired' => 'Vencido',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'paused' => 'Pausado',
                        'expired' => 'Vencido',
                    ]),
                SelectFilter::make('business_id')
                    ->label('Negocio')
                    ->relationship('business', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                TernaryFilter::make('expiring')
                    ->label('Por vencer')
                    ->queries(
                        true: fn ($query) => $query->whereBetween('expires_at', [now()->toDateString(), now()->addDays(7)->toDateString()]),
                        false: fn ($query) => $query,
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                Action::make('pause')
                    ->label('Pausar')
                    ->icon(Heroicon::OutlinedPauseCircle)
                    ->color('warning')
                    ->visible(fn (Coupon $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record): void {
                        $record->update([
                            'status' => 'paused',
                            'moderated_by' => auth()->id(),
                        ]);
                        Audit::log('coupon.paused', $record);
                        Notification::make()->title('Cupón pausado')->warning()->send();
                    }),
                Action::make('activate')
                    ->label('Activar')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('success')
                    ->visible(fn (Coupon $record) => $record->status === 'paused')
                    ->action(function (Coupon $record): void {
                        $record->update([
                            'status' => 'active',
                            'force_expired_at' => null,
                            'moderated_by' => auth()->id(),
                        ]);
                        Audit::log('coupon.activated', $record);
                        Notification::make()->title('Cupón activado')->success()->send();
                    }),
                Action::make('forceExpire')
                    ->label('Forzar vencimiento')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(fn (Coupon $record) => $record->status !== 'expired')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record): void {
                        $record->update([
                            'status' => 'expired',
                            'force_expired_at' => now(),
                            'moderated_by' => auth()->id(),
                        ]);
                        Audit::log('coupon.force_expired', $record);
                        Notification::make()->title('Cupón vencido forzosamente')->danger()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin()),
                ]),
            ])
            ->emptyStateHeading('Sin cupones')
            ->emptyStateDescription('Los cupones publicados por negocios aparecerán aquí.');
    }
}
