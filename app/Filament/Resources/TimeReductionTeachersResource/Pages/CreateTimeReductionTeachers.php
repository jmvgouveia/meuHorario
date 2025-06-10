<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TimeReduction;
use App\Models\TeacherHourCounter;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;


class CreateTimeReductionTeachers extends CreateRecord
{
    protected static string $resource = TimeReductionTeachersResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $teacherId = $data['id_teacher'];
        $reduction = TimeReduction::find($data['id_time_reduction']);

        if (!$reduction) {

            Notification::make()
                ->title('Erro')
                ->body('Tipo de redução não encontrado.')
                ->danger()
                ->send();

            throw new Halt('Tipo de redução não encontrado.');
        }

        $counter = TeacherHourCounter::where('id_teacher', $teacherId)->first();

        if (!$counter) {

            Notification::make()
                ->title('Erro')
                ->body('Contador de horas não encontrado para o professor.')
                ->danger()
                ->send();

            throw new Halt('Contador de horas não encontrado para o professor.');
        }

        $valorLetiva = floatval($reduction->time_reduction_value ?? 0);
        //$valorNaoLetiva = floatval($reduction->time_reduction_value_nl ?? 0);

        if ($valorLetiva > $counter->carga_componente_letiva) {


            Notification::make()
                ->title('Erro')
                ->body('O professor não tem horas disponíveis suficientes para aplicar esta redução.')
                ->danger()
                ->send();


            throw new Halt('O professor não tem horas disponíveis suficientes para aplicar esta redução.');
        }

        return $data;
    }


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
