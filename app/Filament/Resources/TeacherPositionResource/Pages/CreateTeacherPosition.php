<?php

namespace App\Filament\Resources\TeacherPositionResource\Pages;

use App\Filament\Resources\TeacherPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TeacherHourCounter;
use App\Models\TeacherPosition;
use Illuminate\Support\Facades\Log;

class CreateTeacherPosition extends CreateRecord
{
    protected static string $resource = TeacherPositionResource::class;

    protected function afterCreate(): void
    {

        $record = $this->record;

        $teacherId = $record->id_teacher;
        $reduction = \App\Models\Position::find($record->id_position); // CORRIGIDO AQUI

        if ($teacherId && $reduction) {
            $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacherId)->first();

            if ($counter) {
                $valorLetiva = floatval($reduction->position_reduction_value ?? 0);
                $valorNaoLetiva = floatval($reduction->position_reduction_value_nl ?? 0);

                $novaLetiva = max(0, $counter->carga_componente_letiva - $valorLetiva);
                $novaNaoLetiva = max(0, $counter->carga_componente_naoletiva - $valorNaoLetiva);

                $counter->carga_componente_letiva = $novaLetiva;
                $counter->carga_componente_naoletiva = $novaNaoLetiva;
                $counter->carga_horaria = $novaLetiva + $novaNaoLetiva;

                $counter->save();

                Log::info('Counter atualizado com sucesso.');
            } else {
                Log::warning('Contador não encontrado para o professor.');
            }
        } else {
            Log::warning('Dados insuficientes para processar redução.');
        }
    }
}
