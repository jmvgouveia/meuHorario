<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Filament\Events\ServingFilament;
use App\Livewire\NotificationBell;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // \Filament\Facades\Filament::serving(function () {
        //     \Filament\Facades\Filament::getCurrentPanel()?->topNavigation([
        //         NotificationBell::class,
        //     ]);
        // });
    }
}
