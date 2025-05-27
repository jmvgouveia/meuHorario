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
//
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Placeholder;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\Actions as ActionGroup;

use Filament\Forms\Components\Actions\Action;



class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;
    protected $listeners = ['botaoSolicitarTrocaClicado' => 'onSolicitarTrocaClicado'];
    public ?string $justification = null;

    //  Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;
    use InteractsWithActions;


    //preencher automaticamente ano letivo e professor
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

    //Intercepta tentativa de criação para verificar conflito
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $this->conflictingSchedule = Schedules::with('teacher', 'room')
            ->where('id_room', $data['id_room'])
            ->where('id_weekday', $data['id_weekday'])
            ->where('id_timeperiod', $data['id_timeperiod'])
            ->first();

        if ($this->conflictingSchedule) {
            logger('Conflito de horário detectado');

            $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';


            Notification::make()
                ->title('Conflito de horário detetado')
                ->body("Já existe um agendamento para esta sala com $prof")
                ->warning()
                ->persistent()
                // ->actions([
                //     NotificationAction::make('solicitarTroca')
                //         ->label('Solicitar troca')
                //         ->button()
                //         ->color('warning')
                //         ->dispatch('botaoSolicitarTrocaClicado'),

                //     NotificationAction::make('cancelar')
                //         ->label('Cancelar')
                //         ->button()
                //         ->color('secondary')
                //         ->close(),
                // ])
                ->send();

            throw new Halt('Erro ao criar agendamento. Conflito detectado.');
        } else {
            // Se não há conflito, marca como aprovado
            $this->form->fill([
                'status' => 'Aprovado',  // Ajusta para o campo correto e valor adequado
            ]);
        }
    }


    public function submitJustification(array $data)
    {
        // Este é o estado do formulário principal (id_subject, turno, etc.)
        $formState = $this->form->getState();

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        $activeYear = SchoolYears::where('active', true)->first();

        $schedule = Schedules::create([
            'id_room' => $this->conflictingSchedule->id_room,
            'id_weekday' => $this->conflictingSchedule->id_weekday,
            'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
            'id_teacher' => $teacher?->id,
              logger('id_teacher',[$teacher?->id] ),
            'id_subject' => $formState['id_subject'] ?? null,
            'turno' => $formState['turno'] ?? null,
            'id_schoolyear' => $activeYear?->id,
            'status' => 'Pendente',
        ]);

        ScheduleRequest::create([
            'id_schedule_conflict' => $this->conflictingSchedule->id,
            'id_teacher_requester' => $teacher?->id, 
            //   logger('id_teacher_requester',[$teacher?->id] ),
            // 'id_teacher_owner' => $this->conflictingSchedule->id_teacher,
            // logger('id_teacher_owner',[$this->conflictingSchedule->id_teacher] ),
            'id_schedule_novo' => $schedule->id,
            'justification' => $data['justification'] ?? 'Conflito detetado automaticamente.',
            'status' => 'Pendente',
        ]);

        Notification::make()
            ->title('Pedido de troca criado')
             ->body("O seu pedido de troca foi criado com sucesso para o conflito.")
            ->success()
            ->send();
    }




    // public function onSolicitarTrocaClicado()
    // {
    //     logger('Botão de solicitar troca foi clicado.');


    //     $data = $this->form->getState();

    //     $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
    //     $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';
    //     $activeYear = SchoolYears::where('active', true)->first();

    //     // Atualizar o agendamento com conflito para estado "Pendente"
    //     //$this->conflictingSchedule->status = 'Pendente';
    //     //$this->conflictingSchedule->save();

    //     $schedule = Schedules::create([
    //         'id_room' => $this->conflictingSchedule->id_room,
    //         'id_weekday' => $this->conflictingSchedule->id_weekday,
    //         'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
    //         'id_teacher' => $teacher?->id,
    //         'id_subject' => $data['id_subject'],
    //         'turno' => $data['turno'],
    //         'id_schoolyear' => $activeYear?->id,
    //         'status' => 'Pendente', // ou outro estado apropriado
    //     ]);


    //     // Criar pedido de troca
    //     $scheduleRequest = ScheduleRequest::create([
    //         'id_schedule_conflict' => $this->conflictingSchedule->id,
    //         'id_teacher_requester' => $teacher?->id,
    //         'id_schedule_novo' => $schedule->id,
    //         'justification' => 'Conflito detetado automaticamente.',
    //         'status' => 'Pendente',
    //     ]);

    //     Notification::make()
    //         ->title('Pedido de troca criado')
    //         ->body("Foi criado um pedido de troca pendente para o conflito com $prof.")
    //         ->success()
    //         ->actions([
    //             NotificationAction::make('verPedido')
    //                 ->label('Ver pedido')
    //                 ->button()
    //                 ->color('warning')
    //             // ->url(route('filament.resources.schedule-requests.edit', ['record' => $scheduleRequest->id])),
    //         ])
    //         ->send();

    //     //throw new Halt('Agendamento suspenso devido a pedido de troca pendente.');
    // }

    public function getFormSchema(): array
    {

        return [
            // ... outros campos do formulário

            Forms\Components\Actions::make([
                Action::make('justificarConflito')
                    ->label('Justificar Conflito')
                    ->visible(fn() => $this->conflictingSchedule !== null)
                    ->modalHeading('Justificação do Conflito')
                    ->modalSubmitActionLabel('Submeter Justificação')
                    ->modalCancelActionLabel('Cancelar')
                    ->form([
                        Textarea::make('justification')
                            ->label('Escreva a justificação')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(fn(array $data) => $this->submitJustification($data)),
            ]),
        ];
    }
}
