<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Reports;
use App\Filament\Admin\Pages\SiteSettings;
use App\Filament\Admin\Widgets\AdminStatsOverview;
use App\Filament\Admin\Widgets\ExpiringCouponsWidget;
use App\Filament\Admin\Widgets\PendingBusinessesWidget;
use App\Filament\Pages\Auth\EditProfile;
use App\Support\PanelBrand;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('DescuentoNic')
            ->brandLogo(PanelBrand::logoHtml('Admin'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon.ico'))
            ->login()
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
                NavigationGroup::make('Moderación')->collapsible(false),
                NavigationGroup::make('Catálogo'),
                NavigationGroup::make('Operación'),
                NavigationGroup::make('Sistema'),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
                Reports::class,
                SiteSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                AdminStatsOverview::class,
                PendingBusinessesWidget::class,
                ExpiringCouponsWidget::class,
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
