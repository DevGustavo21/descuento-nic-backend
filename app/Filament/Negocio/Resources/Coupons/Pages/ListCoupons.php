<?php

namespace App\Filament\Negocio\Resources\Coupons\Pages;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => CouponResource::canCreate()),
        ];
    }
}
