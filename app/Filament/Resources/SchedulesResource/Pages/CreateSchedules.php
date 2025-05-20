<?php

namespace App\Filament\Resources\SchedulesResource\Pages;

use App\Filament\Resources\SchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SchoolYears;
use App\Models\Teacher;
use App\Models\Schedules;
use App\Models\ScheduleRequest;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Actions\Action;




class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;

    //  Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;
    //public string $justification = '';
    //public bool $mostrarModalConflito = false;
    // public bool $mostrarSolicitarTroca = false;


    protected function shouldOpenModalOnCreate(): bool
    {
        return false;
    }

    // Preencher automaticamente ano letivo e professor
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $activeYear = SchoolYears::where('active', true)->first();
        if ($activeYear) {
            $data['id_schoolyear'] = $activeYear->id;
        }

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        if ($teacher) {
            $data['id_teacher'] = $teacher->id;
        }

        return $data;
    }

    // Intercepta tentativa de criação para verificar conflito
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $this->conflictingSchedule = Schedules::with(['teacher',  'room'])
            ->where('id_room', $data['id_room'])
            ->where('id_weekday', $data['id_weekday'])
            ->where('id_timeperiod', $data['id_timeperiod'])
            ->first();

        if ($this->conflictingSchedule) {
            //$this->mostrarModalConflito = true;
            // $this->mostrarSolicitarTroca = true;

            $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';

            Notification::make()
                ->title('Conflito de horário detetado')
                ->body("Já existe um agendamento para esta sala com $prof.")
                ->warning()
                ->persistent() // fica visível até o utilizador fechar
                ->send();

            throw new Halt(); // ← impede gravação automática
        }
        $this->dispatch('$refresh');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        // 1. Gravar turmas na pivot schedule_class
        $classIds = $this->data['id_classes'] ?? [];

        if (!empty($classIds)) {
            $record->classes()->sync($classIds);
        }

        // 2. Gravar alunos na pivot schedule_student
        $studentIds = $this->data['alunos'] ?? [];

        if (!empty($studentIds)) {
            $record->students()->sync($studentIds);
        }
    }

    //     public function solicitarTroca()
    //     {
    //         // Lógica para criar pedido de troca
    //         Notification::make()
    //             ->title('Pedido de troca registado')
    //             ->success()
    //             ->send();

    //         // Opcional: resetar o estado
    //         $this->mostrarSolicitarTroca = false;
    //     }
}
