<?php

namespace App\Filament\Widgets;

use Illuminate\Contracts\View\View;
use Filament\Widgets\Widget;
use App\Models\Schedules;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\TeacherPosition;
use Filament\Facades\Filament;
use App\Models\TimeReductionTeachers; // Importar o modelo


class ResumoWidget extends Widget
{
    protected static string $view = 'filament.widgets.resumo-widget';

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
            return view(static::$view, ['resumo' => []]);
        }

        // Marcações aprovadas
        $schedules = Schedules::with('subject')
            ->where('status', 'Aprovado')
            ->where('id_teacher', $teacher->id)
            ->get();

        // Contador
        $counter = TeacherHourCounter::where('id_teacher', $teacher->id)->first();
        $letivaDisponivel = $counter?->carga_componente_letiva ?? 0;
        $naoLetivaDisponivel = $counter?->carga_componente_naoletiva ?? 0;

        // Cálculo de aulas
        $aulasLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'letiva')->count();
        $aulasNaoLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'nao letiva')->count();

        // Cargos
        $cargos = TeacherPosition::with('position')
            ->where('id_teacher', $teacher->id)
            ->get()
            ->map(function ($cargo) {
                return [
                    'nome' => $cargo->position->position,
                    'redução_letiva' => $cargo->position->position_reduction_value ?? 0,
                    'redução_naoletiva' => $cargo->position->position_reduction_value_nl ?? 0,
                ];
            })->toArray();

        // Reduções por tempo de serviço
        $reducoesTempo = TimeReductionTeachers::with('timeReduction')
            ->where('id_teacher', $teacher->id)
            ->get();

        $totalReducoesTempo = $reducoesTempo->sum(fn($r) => $r->timeReduction->reduction_value ?? 0);
        // Montar resumo
        $resumo = [
            'letiva' => $aulasLetivas,
            'nao_letiva' => $aulasNaoLetivas,
            'disponivel_letiva' => max(0, $letivaDisponivel),
            'disponivel_naoletiva' => max(0, $naoLetivaDisponivel),
            'cargos' => $cargos,
            'reducoes' => $totalReducoesTempo,

        ];

        return view(static::$view, compact('resumo'));
    }
}
