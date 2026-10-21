<?php

namespace App\Filament\Admin\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->copyable(),
                TextColumn::make('title')->label('Título')->searchable()->limit(40),
                TextColumn::make('business.name')->label('Negocio')->searchable(),
                TextColumn::make('discount_value')
                    ->label('Descuento')
                    ->formatStateUsing(fn ($state, $record) => $record->discountLabel()),
                TextColumn::make('expires_at')->label('Vence')->date('d M Y'),
                TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->formatStateUsing(fn ($state, $record) => $state.'/'.$record->max_redemptions),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
