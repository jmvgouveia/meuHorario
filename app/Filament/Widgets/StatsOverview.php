<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Department;
use App\Models\Docente;
use App\Models\Room;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Edifícios', Building::count())
                ->description('Número total de edifícios')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Total de Salas', Room::count())
                ->description('Número total de salas')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Média de Salas', number_format(Room::count() / max(Building::count(), 1), 1))
                ->description('Média de salas por edifício')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Numero de Docentes', Teacher::count())
                ->description('Número total de docentes')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger'),


            Stat::make('Numero de Alunos', Student::count())
                ->description('Número total de Alunos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Numero de Deparmentos', Department::count())
                ->description('Número total de Departamentos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Numero de Cursos', Course::count())
                ->description('Número total de Cursos')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Numero de Turmas', Classes::count())
                ->description('Número total de Turmas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

        ];
    }
}
