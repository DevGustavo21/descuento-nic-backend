<?php

namespace App\Filament\Negocio\Widgets;

use App\Filament\Negocio\Pages\BusinessProfile;
use App\Filament\Negocio\Resources\Coupons\CouponResource;
use App\Models\Business;
use Filament\Widgets\Widget;

class BusinessStatusWidget extends Widget
{
    protected string $view = 'filament.negocio.widgets.business-status';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getBusiness(): ?Business
    {
        return auth()->user()?->business;
    }

    /**
     * @return array{tone: string, title: string, body: string, cta_label: ?string, cta_url: ?string}
     */
    public function getStatusCard(): array
    {
        $business = $this->getBusiness();

        if (! $business) {
            return [
                'tone' => 'warning',
                'title' => 'Sin negocio vinculado',
                'body' => 'Tu cuenta no está asociada a un negocio. Contactá a soporte de DescuentoNic.',
                'cta_label' => null,
                'cta_url' => null,
            ];
        }

        return match ($business->status) {
            'approved' => [
                'tone' => 'success',
                'title' => $business->name.' está aprobado',
                'body' => $business->isFeaturedNow()
                    ? 'Tu negocio aparece como destacado en el catálogo. Podés publicar y gestionar cupones.'
                    : 'Ya podés publicar cupones y registrar canjes desde este panel.',
                'cta_label' => CouponResource::canCreate() ? 'Crear cupón' : null,
                'cta_url' => CouponResource::canCreate() ? CouponResource::getUrl('create') : null,
            ],
            'rejected' => [
                'tone' => 'danger',
                'title' => 'Solicitud rechazada',
                'body' => 'Motivo: '.($business->rejection_reason ?: 'No especificado').' Actualizá los datos de tu negocio y pedí una nueva revisión.',
                'cta_label' => 'Editar perfil del negocio',
                'cta_url' => BusinessProfile::getUrl(),
            ],
            default => [
                'tone' => 'warning',
                'title' => 'En revisión',
                'body' => 'Recibimos la solicitud de '.$business->name.'. Cuando un administrador la apruebe, vas a poder publicar cupones.',
                'cta_label' => 'Ver / completar perfil',
                'cta_url' => BusinessProfile::getUrl(),
            ],
        };
    }
}
