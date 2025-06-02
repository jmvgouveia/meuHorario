<?php

namespace App\Filament\Widgets;

use Illuminate\Contracts\View\View;

use Filament\Widgets\Widget;
use App\Models\Schedules;
use App\Models\Teacher;
use App\Models\WeekDays;
use App\Models\TimePeriod;
use Filament\Facades\Filament;  // <-- Importar aqui


class WeeklyScheduleWidget extends Widget
{
    protected static string $view = 'filament.widgets.weekly-schedule-widget';

    protected int | string | array $columnSpan = [
        'sm' => 12,
        'md' => 6,
        'lg' => 'full',
    ];

    public function render(): View

    {
        $userId = Filament::auth()->id();
        $teacher = Teacher::where('id_user', $userId)->first();

        if (! $teacher) {
            // Se não tem professor vinculado, retorna view vazia
            return view(static::$view, [
                'calendar' => [],
                'weekdays' => [],
                'timePeriods' => [],
            ]);
        }

        // Busca as marcações aprovadas do professor
        $schedules = Schedules::with(['room', 'weekday', 'timePeriod', 'subject'])
            ->where('status', 'Aprovado')
            ->where('id_teacher', $teacher->id)
            ->get();

        // Pegamos os dias da semana da tabela Weekday (ajusta se for outro nome)
        $weekdays = WeekDays::orderBy('id')->pluck('weekday')->toArray();

        // Pegamos todos os períodos de tempo ordenados
        $timePeriods = TimePeriod::orderBy('description')->get();

        // Monta matriz vazia do calendário: [periodo][dia] => schedule|null
        $calendar = [];

        foreach ($timePeriods as $tp) {
            foreach ($weekdays as $day) {
                $calendar[$tp->description][$day] = null;
            }
        }

        // Preenche o calendário com as marcações do professor
        foreach ($schedules as $schedule) {
            $day = $schedule->weekday->weekday;
            $time = $schedule->timePeriod->description;

            if (in_array($day, $weekdays)) {
                $calendar[$time][$day] = $schedule;
            }
        }

        return view(static::$view, compact('calendar', 'weekdays', 'timePeriods'));
    }
}
