<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BuildingsOverview;
use App\Filament\Widgets\StatsOverview;
use App\Models\Docente;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            BuildingsOverview::class,
            Docente::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
