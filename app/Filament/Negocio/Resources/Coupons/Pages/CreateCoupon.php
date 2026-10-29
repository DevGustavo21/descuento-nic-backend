<?php

namespace App\Filament\Negocio\Resources\Coupons\Pages;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected ?string $heading = 'Crear cupón';

    protected ?string $subheading = 'Definí la oferta, vigencia y canales. El código se genera solo.';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['business_id'] = $user->business_id;
        $data['category_id'] = $user->business?->category_id;
        $data['code'] = \App\Models\Coupon::generateCode();

        return $data;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Publicar cupón')
            ->icon(Heroicon::OutlinedCheck)
            ->color('success');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Publicar y crear otro')
            ->color('gray')
            ->outlined();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->color('gray')
            ->outlined();
    }
}
