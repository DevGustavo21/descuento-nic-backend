<?php

namespace App\Providers\Filament;

use App\Filament\Negocio\Pages\BusinessProfile;
use App\Filament\Negocio\Pages\CanjearCupon;
use App\Filament\Negocio\Pages\Dashboard;
use App\Filament\Negocio\Widgets\BusinessStatusWidget;
use App\Filament\Negocio\Widgets\CouponStatsWidget;
use App\Filament\Negocio\Widgets\ExpiringCouponsWidget;
use App\Filament\Negocio\Widgets\RecentRedemptionsWidget;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\RegisterBusiness;
use App\Support\PanelBrand;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class NegocioPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('negocio')
            ->path('negocio')
            ->brandName('DescuentoNic Negocios')
            ->brandLogo(PanelBrand::logoHtml('Negocios'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon.ico'))
            ->login()
            ->registration(RegisterBusiness::class)
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->darkMode(false)
            ->font('Plus Jakarta Sans', provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::hex('#E11D48'),
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Teal,
                'info' => Color::Sky,
                'gray' => Color::Slate,
            ])
            ->navigationGroups([
                NavigationGroup::make('Cupones')->collapsible(false),
                NavigationGroup::make('Negocio'),
            ])
            ->discoverResources(in: app_path('Filament/Negocio/Resources'), for: 'App\\Filament\\Negocio\\Resources')
            ->discoverPages(in: app_path('Filament/Negocio/Pages'), for: 'App\\Filament\\Negocio\\Pages')
            ->pages([
                Dashboard::class,
                CanjearCupon::class,
                BusinessProfile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Negocio/Widgets'), for: 'App\\Filament\\Negocio\\Widgets')
            ->widgets([
                BusinessStatusWidget::class,
                CouponStatsWidget::class,
                ExpiringCouponsWidget::class,
                RecentRedemptionsWidget::class,
                AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => Blade::render('@include(\'filament.hooks.impersonation-banner\')'),
            )
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
