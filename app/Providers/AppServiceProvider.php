<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Filament\Widgets\WeeklyScheduleWidget;
use Filament\Facades\Filament;
use App\Models\ScheduleRequest;
use App\Observers\ScheduleRequestObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ScheduleRequest::observe(ScheduleRequestObserver::class);
    }
}
