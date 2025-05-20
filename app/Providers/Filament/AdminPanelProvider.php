<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;

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
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\BuildingsOverview;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Pages\Dashboard;
use Filament\SpatieLaravelPermissionPlugin\SpatieLaravelPermissionPlugin;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\RoleResource;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\SchedulesResource;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->colors([
                'primary' => Color::Amber,
            ])

            // ---          Acesso ao painel por perfil
            // ->navigationItems([
            //     NavigationItem::make('Permissões')
            //         ->url(fn() => route('filament.admin.resources.permissions.index'))
            //         ->icon('heroicon-o-key')
            //         ->visible(fn() => Auth::check() && Auth::user()?->hasRole('admin')),

            //     NavigationItem::make('Papeis')
            //         ->url(fn() => route('filament.admin.resources.roles.index'))
            //         ->icon('heroicon-o-shield-check')
            //         ->visible(fn() => Auth::check() && Auth::user()?->hasRole('admin')),

            //     // NavigationItem::make('Horários')
            //     //     ->url(fn() => route('filament.admin.resources.schedules.index'))
            //     //     ->icon('heroicon-o-calendar')
            //     //     ->visible(fn() => auth()->user()?->can('ver_horarios')),





            // ])
            // ---
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                StatsOverview::class,
                BuildingsOverview::class,
            ])
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
            ])
            //    ->plugins([
            //        SpatieLaravelPermissionPlugin::make(),
            //    ]);
        ;
    }
}
