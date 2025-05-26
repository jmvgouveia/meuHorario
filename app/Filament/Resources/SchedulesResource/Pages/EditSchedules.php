<?php

namespace App\Filament\Resources\SchedulesResource\Pages;

use App\Filament\Resources\SchedulesResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\Schedule;


class EditSchedules extends EditRecord
{
    protected static string $resource = SchedulesResource::class;


    public ?Schedule $conflictingSchedule = null;


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se quiseres mudar algo antes de salvar os campos do modelo, faz aqui.
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);

        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }
}
