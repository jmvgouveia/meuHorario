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




class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;

    // 🔸 Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;
    public string $justification = '';
    public bool $mostrarModalConflito = false;

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

    // // Intercepta tentativa de criação para verificar conflito
    // protected function beforeCreate(): void
    // {
    //     $data = $this->form->getState();

    //     $this->conflictingSchedule = Schedules::with(['teacher', 'subject', 'room'])
    //         ->where('id_room', $data['id_room'])
    //         ->where('id_weekday', $data['id_weekday'])
    //         ->where('id_timeperiod', $data['id_timeperiod'])
    //         ->first();

    //     if ($this->conflictingSchedule) {
    //         $this->mostrarModalConflito = true;
    //         throw new Halt(); // ← impede gravação automática
    //     }
    // }


    // protected function afterCreate(): void
    // {
    //     $record = $this->record;

    //     // 1. Gravar turmas na pivot schedule_class
    //     $classIds = $this->data['id_classes'] ?? [];

    //     if (!empty($classIds)) {
    //         $record->classes()->sync($classIds);
    //     }

    //     // 2. Gravar alunos na pivot schedule_student
    //     $studentIds = $this->data['alunos'] ?? [];

    //     if (!empty($studentIds)) {
    //         $record->students()->sync($studentIds);
    //     }
    // }

    // public function confirmarTroca(array $modalData): void
    // {
    //     $formData = $this->form->getState();

    //     // Gravar novo horário
    //     $novoHorario = Schedules::create($this->mutateFormDataBeforeCreate($formData));

    //     // Registar pedido de troca
    //     ScheduleRequest::create([
    //         'id_schedule_conflict' => $this->conflictingSchedule->id,
    //         'id_schedule_novo' => $novoHorario->id,
    //         'id_teacher_requester' => Teacher::where('id_user', Filament::auth()->id())->first()->id,
    //         'justification' => $modalData['justification'],
    //     ]);

    //     Notification::make()
    //         ->title('Pedido de troca enviado')
    //         ->success()
    //         ->send();

    //     $this->redirect(SchedulesResource::getUrl('index'));
    // }

    // // Mostra o botão de ação do modal apenas se houver conflito
    // protected function getFormActions(): array
    // {
    //     $actions = parent::getFormActions();

    //     if ($this->mostrarModalConflito && $this->conflictingSchedule) {
    //         $conf = $this->conflictingSchedule;

    //         $descricao = sprintf(
    //             "Disciplina: %s\nProfessor: %s\nSala: %s",
    //             $conf->subject->subject ?? '—',
    //             $conf->teacher->name ?? '—',
    //             $conf->room->name ?? '—',
    //         );

    //         $actions[] = Actions\Action::make('confirmarTroca')
    //             ->label('Solicitar troca')
    //             ->modalHeading('Conflito detectado')
    //             ->modalSubheading('Já existe uma marcação neste horário.')
    //             ->modalDescription(nl2br($descricao))
    //             ->form([
    //                 Textarea::make('justification')
    //                     ->label('Justificação')
    //                     ->required(),
    //             ])
    //             ->action(fn(array $data) => $this->confirmarTroca($data));
    //     }

    //     return $actions;
    // }
}
