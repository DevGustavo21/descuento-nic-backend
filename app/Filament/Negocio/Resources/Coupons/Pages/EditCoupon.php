<?php

namespace App\Filament\Negocio\Resources\Coupons\Pages;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveHeader')
                ->label('Guardar')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->action('save'),
            DeleteAction::make()
                ->label('Borrar')
                ->color('danger'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar')
            ->icon(Heroicon::OutlinedCheck)
            ->color('success');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->color('gray')
            ->outlined();
    }
}
