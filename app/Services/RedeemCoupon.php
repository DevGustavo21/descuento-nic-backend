<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Redemption;
use App\Support\Phone;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RedeemCoupon
{
    /**
     * @return array{redemption: Redemption, coupon: Coupon}
     */
    public function handleForBusiness(
        int $businessId,
        string $code,
        string $customerPhone,
        string $source = 'in_store',
        ?string $notes = null,
    ): array {
        $normalized = strtoupper(preg_replace('/\s+/', '', $code) ?? '');

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'code' => 'Ingresá el código del cupón.',
            ]);
        }

        $coupon = Coupon::query()
            ->where('business_id', $businessId)
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => 'No encontramos ese código entre tus cupones.',
            ]);
        }

        return $this->handle($coupon, $customerPhone, $source, $notes);
    }

    /**
     * @return array{redemption: Redemption, coupon: Coupon}
     */
    public function handle(Coupon $coupon, string $customerPhone, string $source = 'in_store', ?string $notes = null): array
    {
        $phone = Phone::normalize($customerPhone);

        if (! $phone || strlen($phone) < 8) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Ingresá un teléfono válido (mínimo 8 dígitos).',
            ]);
        }

        if ($coupon->status !== 'active' || $coupon->force_expired_at) {
            throw ValidationException::withMessages([
                'coupon' => 'Este cupón no está activo.',
            ]);
        }

        if ($coupon->expires_at->endOfDay()->isPast()) {
            throw ValidationException::withMessages([
                'coupon' => 'Este cupón ya venció.',
            ]);
        }

        if ($coupon->remainingRedemptions() <= 0) {
            throw ValidationException::withMessages([
                'coupon' => 'Este cupón ya no tiene canjes disponibles.',
            ]);
        }

        $already = Redemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('customer_phone', $phone)
            ->exists();

        if ($already) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Este teléfono ya canjeó este cupón.',
            ]);
        }

        try {
            return DB::transaction(function () use ($coupon, $phone, $source, $notes) {
                $locked = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->firstOrFail();

                if ($locked->remainingRedemptions() <= 0) {
                    throw ValidationException::withMessages([
                        'coupon' => 'Este cupón ya no tiene canjes disponibles.',
                    ]);
                }

                $redemption = Redemption::query()->create([
                    'coupon_id' => $locked->id,
                    'business_id' => $locked->business_id,
                    'source' => $source,
                    'notes' => $notes,
                    'customer_phone' => $phone,
                    'redeemed_at' => now(),
                ]);

                $locked->increment('redemptions_count');
                $locked->refresh();

                return [
                    'redemption' => $redemption,
                    'coupon' => $locked,
                ];
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Este teléfono ya canjeó este cupón.',
            ]);
        }
    }
}
