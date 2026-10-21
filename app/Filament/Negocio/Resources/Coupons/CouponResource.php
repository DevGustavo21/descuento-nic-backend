<?php

namespace App\Filament\Negocio\Resources\Coupons;

use App\Filament\Concerns\ConfiguresCouponForm;
use App\Filament\Negocio\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Negocio\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Negocio\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\Redemption;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponResource extends Resource
{
    use ConfiguresCouponForm;

    protected static ?string $model = Coupon::class;

    protected static ?string $modelLabel = 'cupón';

    protected static ?string $pluralModelLabel = 'cupones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::couponFormComponents());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->copyable()->searchable(),
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('discount_value')
                    ->label('Descuento')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->discountLabel()),
                TextColumn::make('expires_at')->label('Vence')->date('d M Y'),
                TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->formatStateUsing(fn ($state, Coupon $record) => $state.' / '.$record->max_redemptions),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('redeem')
                    ->label('Registrar canje')
                    ->icon(Heroicon::OutlinedCheck)
                    ->requiresConfirmation()
                    ->visible(fn (Coupon $record) => $record->remainingRedemptions() > 0 && $record->status === 'active')
                    ->action(function (Coupon $record): void {
                        Redemption::create([
                            'coupon_id' => $record->id,
                            'business_id' => $record->business_id,
                            'source' => 'in_store',
                            'redeemed_at' => now(),
                        ]);

                        $record->increment('redemptions_count');

                        Notification::make()
                            ->title('Canje registrado')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
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
