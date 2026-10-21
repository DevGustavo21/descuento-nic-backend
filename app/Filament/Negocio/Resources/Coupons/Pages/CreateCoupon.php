<?php

namespace App\Filament\Negocio\Resources\Coupons\Pages;

use App\Filament\Negocio\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

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
}
