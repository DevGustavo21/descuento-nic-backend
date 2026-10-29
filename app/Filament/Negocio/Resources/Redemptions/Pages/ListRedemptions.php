<?php

namespace App\Filament\Negocio\Resources\Redemptions\Pages;

use App\Filament\Negocio\Resources\Redemptions\RedemptionResource;
use Filament\Resources\Pages\ListRecords;

class ListRedemptions extends ListRecords
{
    protected static string $resource = RedemptionResource::class;

    protected ?string $subheading = 'Historial de canjes de tus cupones.';
}
