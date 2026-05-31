<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile(\App\Filament\Pages\EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])

            // ── CSS del proyecto (Tailwind v4 compilado) ───────────────────
            // Carga app.css para que las clases Tailwind personalizadas funcionen
            // en todos los componentes Blade del panel (widgets, POS, etc.).
            // Si el build no existe aún, se ignora silenciosamente.
            ->renderHook(
                'panels::styles.after',
                function (): HtmlString {
                    try {
                        return new HtmlString(app(\Illuminate\Foundation\Vite::class)('resources/css/app.css'));
                    } catch (\Exception) {
                        return new HtmlString('');
                    }
                },
            )

            // ── CSS base del sistema (independiente del build de Tailwind) ─
            // Todo esto usa CSS nativo para no depender de que el proyecto
            // tenga npm run build ejecutado.
            ->renderHook(
                'panels::head.end',
                function (): HtmlString {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $isStaff = $user && method_exists($user, 'hasAnyRole')
                        && $user->hasAnyRole(['barbero', 'cajero']);

                    $sidebarCss = $isStaff ? '
/* ── Ocultar sidebar de Filament para barbero/cajero en móvil ─── */
@media(max-width:1023px){
    .fi-main-sidebar,
    .fi-sidebar-close-overlay          { display:none!important; }
    .fi-topbar-open-sidebar-btn,
    .fi-topbar-close-sidebar-btn       { display:none!important; }
    .fi-main-ctn                       { padding-left:0!important; }
    .fi-main-ctn-sidebar-open          { padding-left:0!important;margin-left:0!important; }
}' : '';

                    return new HtmlString('<style>
/* ── Ocultar bottom nav en desktop ─────────────────────────────── */
@media(min-width:1024px){
    .barber-mobile-nav { display:none!important; }
}

/* ── Espacio inferior para que el contenido no quede bajo el nav ─ */
/* Se aplica solo en móvil cuando existe el bottom nav */
@media(max-width:1023px){
    .fi-panel-admin .has-mobile-nav .fi-main {
        padding-bottom: 4.5rem !important;
    }
}
' . $sidebarCss . '
</style>');
                },
            )

            // ── Mobile nav (render al final del body) ─────────────────────
            ->renderHook(
                'panels::body.end',
                fn (): \Illuminate\Contracts\View\View => view('filament.components.mobile-nav'),
            )

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
