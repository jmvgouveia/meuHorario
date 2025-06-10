<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TimeReduction;
use App\Models\TeacherHourCounter;
use Illuminate\Support\Facades\Log;


class CreateTimeReductionTeachers extends CreateRecord
{
    protected static string $resource = TimeReductionTeachersResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        $teacherId = $record->id_teacher;
        $reduction = \App\Models\TimeReduction::find($record->id_time_reduction);

        if ($teacherId && $reduction) {
            $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacherId)->first();

            if ($counter) {
                $valorLetiva = floatval($reduction->time_reduction_value ?? 0);
                $valorNaoLetiva = floatval($reduction->time_reduction_value_nl ?? 0);

                // Reduz carga letiva até o mínimo de 0
                $novaLetiva = max(0, $counter->carga_componente_letiva - $valorLetiva);

                // Reduz carga não letiva até o mínimo de 0
                $novaNaoLetiva = max(0, $counter->carga_componente_naoletiva - $valorNaoLetiva);

                $counter->carga_componente_letiva = $novaLetiva;
                $counter->carga_componente_naoletiva = $novaNaoLetiva;
                $counter->carga_horaria = $novaLetiva + $novaNaoLetiva;

                $counter->save();
            }
        }
    }
}
