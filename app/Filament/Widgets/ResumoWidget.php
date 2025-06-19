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
            ->whereIn('status', ['Aprovado', 'Aprovado DP'])
            ->where('id_teacher', $teacher->id)
            ->get();

        // Contador
        $counter = TeacherHourCounter::where('id_teacher', $teacher->id)->first();
        //  $disponivel = $counter?->carga_horaria ?? 0;
        $letivaDisponivel = $counter?->carga_componente_letiva ?? 0;
        $naoLetivaDisponivel = $counter?->carga_componente_naoletiva ?? 0;

        // Aulas
        $aulasLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'letiva')->count();
        $aulasNaoLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'nao letiva')->count();

        // Cargos com redução
        $cargos = TeacherPosition::with('position')
            ->where('id_teacher', $teacher->id)
            ->get()
            ->map(function ($cargo) {
                return [
                    'nome' => $cargo->position->position,
                    'descricao' => $cargo->position->position_description ?? 'Cargo sem descrição',
                    'redução_letiva' => $cargo->position->position_reduction_value ?? 0,
                    'redução_naoletiva' => $cargo->position->position_reduction_value_nl ?? 0,
                ];
            })->toArray();

        // Reduções por tempo de serviço
        $tempoReducoes = TimeReductionTeachers::with('timeReduction')
            ->where('id_teacher', $teacher->id)
            ->get()
            ->map(function ($reducao) {
                return [
                    'nome' => $reducao->timeReduction->time_reduction ?? 'Redução sem nome',
                    'descricao' => $reducao->timeReduction->time_reduction_description ?? 'Redução sem descrição',
                    'redução_letiva' => $reducao->timeReduction->time_reduction_value ?? 0,
                    'redução_naoletiva' => $reducao->timeReduction->time_reduction_value_nl ?? 0,
                ];
            })->toArray();
        // dd($cargos, $tempoReducoes);
        $resumo = [
            //    'disponivel' => $disponivel,
            'letiva' => $aulasLetivas,
            'nao_letiva' => $aulasNaoLetivas,
            'disponivel_letiva' => max(0, $letivaDisponivel),
            'disponivel_naoletiva' => max(0, $naoLetivaDisponivel),
            'cargos' => $cargos,
            'tempo_reducoes' => $tempoReducoes,
        ];

        return view(static::$view, compact('resumo'));
    }
}
