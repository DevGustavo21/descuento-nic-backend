<?php

namespace App\Filament\Admin\Resources\Coupons\Schemas;

use App\Filament\Concerns\ConfiguresCouponForm;
use Filament\Schemas\Schema;

class CouponForm
{
    use ConfiguresCouponForm;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::couponFormComponents(forAdmin: true));
    }
}
