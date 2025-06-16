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
    protected static bool $isLazy = false; // Para garantir que carrega completamente

    protected int | string | array $columnSpan = [
        'sm' => 12,
        'md' => 12,
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
        $schedules = Schedules::with(['room', 'weekday', 'timePeriod', 'subject', 'classes'])
            //->where('status', 'Aprovado')
            ->where('id_teacher', $teacher->id)
            ->get();

        // Pegamos os dias da semana da tabela Weekday (ajusta se for outro nome)
        $weekdays = WeekDays::orderBy('id')->pluck('weekday')->toArray();

        // Pegamos todos os períodos de tempo ordenados
        $timePeriods = TimePeriod::orderBy('description')->get();

        // Monta matriz vazia do calendário: [periodo][dia] => schedule|null
        $calendar = [];

        foreach ($timePeriods as $tp) {
            foreach (array_keys($weekdays) as $dayId) {
                $calendar[$tp->id][$dayId] = null;
            }
        }
        // Preenche o calendário com as marcações do professor
        foreach ($schedules as $schedule) {
            $dayId = $schedule->id_weekday;
            $timeId = $schedule->id_timeperiod;
            $calendar[$timeId][$dayId] = $schedule;
        }

        $recusados = \App\Models\ScheduleRequest::where('status', 'Recusado')
            ->where('id_teacher_requester', $teacher->id)
            ->get()
            ->keyBy('id_schedule_novo');

        $escalados = \App\Models\ScheduleRequest::where('status', 'Escalado')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule_conflict] = $req;
                $carry[$req->id_schedule_novo] = $req;
                return $carry;
            }, collect());

        $PedidosAprovadosDP = \App\Models\ScheduleRequest::where('status', 'Aprovado DP')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule_conflict] = $req;
                $carry[$req->id_schedule_novo] = $req;
                return $carry;
            }, collect());

        $AprovadosDP = Schedules::where('status', 'Aprovado DP')
            ->get()
            ->keyBy('id');
        // dd($AprovadosDP->keys());

        // dd(Schedules::where('status', 'Aprovado DP')->get());

        // Retorna a view com o calendário, dias da semana e períodos de tempo
        return view(static::$view, compact('calendar', 'weekdays', 'timePeriods', 'recusados', 'escalados', 'PedidosAprovadosDP', 'AprovadosDP'))
            ->with('teacher', $teacher);
    }
}
