<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\TimeReduction;
use App\Models\TeacherHourCounter;
use Illuminate\Support\Facades\Log;


class EditTimeReductionTeachers extends EditRecord
{
    protected static string $resource = TimeReductionTeachersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    $record->load('timeReduction');

                    $reduction = $record->timeReduction;
                    $counter = TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                    if ($reduction && $counter) {
                        $valorLetiva = floatval($reduction->time_reduction_value ?? 0);
                        $valorNaoLetiva = floatval($reduction->time_reduction_value_nl ?? 0);

                        $novaLetiva = $counter->carga_componente_letiva + $valorLetiva;
                        $novaNaoLetiva = $counter->carga_componente_naoletiva + $valorNaoLetiva;

                        $counter->carga_componente_letiva = $novaLetiva;
                        $counter->carga_componente_naoletiva = $novaNaoLetiva;
                        $counter->carga_horaria = $novaLetiva + $novaNaoLetiva;
                        $counter->save();
                    }
                }),
        ];
    }
    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $jaTemReducao = \App\Models\TimeReductionTeachers::where('id_teacher', $data['id_teacher'])
            ->where('id_time_reduction', $data['id_time_reduction'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($jaTemReducao) {
            \Filament\Notifications\Notification::make()
                ->title('Redução duplicada')
                ->body('Este professor já tem esta redução atribuída.')
                ->danger()
                ->persistent()
                ->send();

            throw new \Filament\Support\Exceptions\Halt('O professor já possui esta redução.');
        }
    }
}
