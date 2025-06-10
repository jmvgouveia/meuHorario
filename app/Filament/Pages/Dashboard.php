<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BuildingsOverview;
use App\Filament\Widgets\StatsOverview;
use App\Models\Docente;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\WeeklyScheduleWidget;
use App\Filament\Widgets\ResumoWidget;


class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            WeeklyScheduleWidget::class,
            ResumoWidget::class,
            //WeeklyScheduleWidget::class => ['sort' => 1, 'columnSpan' => 'full'],
            // ResumoWidget::class => ['sort' => 2, 'columnSpan' => 'full'],
            //    StatsOverview::class,
            //  BuildingsOverview::class,
            // Docente::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
