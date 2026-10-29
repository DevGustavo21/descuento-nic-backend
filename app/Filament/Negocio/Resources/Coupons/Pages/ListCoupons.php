<?php

namespace App\Filament\Negocio\Resources\Coupons\Pages;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected ?string $subheading = 'Publicá ofertas, pausalas o registrá canjes en tienda.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear cupón')
                ->icon(Heroicon::OutlinedPlus)
                ->color('success')
                ->visible(fn () => CouponResource::canCreate()),
        ];
    }
}
