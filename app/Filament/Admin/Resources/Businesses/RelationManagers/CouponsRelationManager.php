<?php

namespace App\Filament\Admin\Resources\Businesses\RelationManagers;

use App\Filament\Admin\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'coupons';

    protected static ?string $title = 'Cupones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código'),
                TextColumn::make('title')->label('Título')->limit(40),
                TextColumn::make('discount_value')
                    ->label('Descuento')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->discountLabel()),
                TextColumn::make('expires_at')->label('Vence')->date('d M Y'),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Abrir')
                    ->url(fn (Coupon $record) => CouponResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
