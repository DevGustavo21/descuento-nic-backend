<?php

namespace App\Filament\Negocio\Resources\Coupons;

use App\Filament\Concerns\ConfiguresCouponForm;
use App\Filament\Negocio\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Negocio\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Negocio\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Negocio\Pages\CanjearCupon;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CouponResource extends Resource
{
    use ConfiguresCouponForm;

    protected static ?string $model = Coupon::class;

    protected static ?string $modelLabel = 'cupón';

    protected static ?string $pluralModelLabel = 'cupones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Cupones';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $businessId = auth()->user()?->business_id;

        if (! $businessId) {
            return null;
        }

        $count = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->whereNull('force_expired_at')
            ->whereDate('expires_at', '>=', now())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::couponFormComponents());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->copyable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')->label('Título')->searchable()->limit(40)->wrap(),
                TextColumn::make('discount_value')
                    ->label('Descuento')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->discountLabel()),
                TextColumn::make('expires_at')
                    ->label('Vence')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Coupon $record): string => $record->expires_at->lte(now()->addDays(5)) ? 'warning' : 'gray'),
                TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->formatStateUsing(fn ($state, Coupon $record) => $state.' / '.$record->max_redemptions)
                    ->alignEnd(),
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
            ])
            ->recordActions([
                Action::make('redeem')
                    ->label('Canjear')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Coupon $record) => $record->remainingRedemptions() > 0 && $record->status === 'active' && ! $record->force_expired_at)
                    ->url(fn (Coupon $record): string => CanjearCupon::getUrl(['code' => $record->code], panel: 'negocio')),
                Action::make('pause')
                    ->label('Pausar')
                    ->icon(Heroicon::OutlinedPauseCircle)
                    ->color('gray')
                    ->outlined()
                    ->visible(fn (Coupon $record) => $record->status === 'active')
                    ->action(function (Coupon $record): void {
                        $record->update(['status' => 'paused']);
                        Notification::make()->title('Cupón pausado')->warning()->send();
                    }),
                Action::make('activate')
                    ->label('Activar')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('success')
                    ->visible(fn (Coupon $record) => $record->status === 'paused')
                    ->action(function (Coupon $record): void {
                        $record->update(['status' => 'active']);
                        Notification::make()->title('Cupón activado')->success()->send();
                    }),
                EditAction::make()
                    ->color('gray')
                    ->outlined(),
            ])
            ->emptyStateHeading('Todavía no tenés cupones')
            ->emptyStateDescription('Publicá tu primera oferta para aparecer en el catálogo de DescuentoNic.')
            ->emptyStateIcon(Heroicon::OutlinedTicket)
            ->emptyStateActions([
                Action::make('create')
                    ->label('Crear cupón')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(static::getUrl('create'))
                    ->visible(fn () => static::canCreate()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('business_id', auth()->user()?->business_id);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->business?->isApproved();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
