<?php

namespace App\Filament\Resources\TeacherPositionResource\Pages;

use App\Filament\Resources\TeacherPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\TeacherHourCounter;

class EditTeacherPosition extends EditRecord
{
    protected static string $resource = TeacherPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    // $record->load('timeReduction');

                    $reduction = $record->position;
                    $counter = TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                    if ($reduction && $counter) {
                        $valorLetiva = floatval($reduction->position_reduction_value ?? 0);
                        $valorNaoLetiva = floatval($reduction->position_reduction_value_nl ?? 0);

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

        $jaTemCargo = \App\Models\TeacherPosition::where('id_teacher', $data['id_teacher'])
            ->where('id_position', $data['id_position'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($jaTemCargo) {
            \Filament\Notifications\Notification::make()
                ->title('Cargo duplicado')
                ->body('Este professor já tem este cargo atribuído.')
                ->danger()
                ->persistent()
                ->send();

            throw new \Filament\Support\Exceptions\Halt('O professor já possui este cargo.');
        }
    }
}
