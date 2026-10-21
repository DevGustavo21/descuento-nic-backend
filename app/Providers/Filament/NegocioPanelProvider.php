<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\RegisterBusiness;
use App\Filament\Negocio\Widgets\CouponStatsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class NegocioPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('negocio')
            ->path('negocio')
            ->brandName('DescuentoNic Negocios')
            ->login()
            ->registration(RegisterBusiness::class)
            ->emailVerification()
            ->profile()
            ->darkMode(false)
            ->colors([
                'primary' => Color::hex('#9A4A3C'),
            ])
            ->discoverResources(in: app_path('Filament/Negocio/Resources'), for: 'App\\Filament\\Negocio\\Resources')
            ->discoverPages(in: app_path('Filament/Negocio/Pages'), for: 'App\\Filament\\Negocio\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Negocio/Widgets'), for: 'App\\Filament\\Negocio\\Widgets')
            ->widgets([
                CouponStatsWidget::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
