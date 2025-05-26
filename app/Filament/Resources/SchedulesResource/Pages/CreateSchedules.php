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
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Placeholder;

class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;
    protected $listeners = ['botaoSolicitarTrocaClicado' => 'onSolicitarTrocaClicado'];

    //  Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;
    use InteractsWithActions;


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
            ->actions([
                NotificationAction::make('solicitarTroca')
                    ->label('Solicitar troca')
                    ->button()
                    ->color('warning')
                    ->dispatch('botaoSolicitarTrocaClicado'),

                NotificationAction::make('cancelar')
                    ->label('Cancelar')
                    ->button()
                    ->color('secondary')
                    ->close(),
            ])
            ->send();

        throw new Halt('Erro ao criar agendamento. Conflito detectado.');
    } else {
        // Se não há conflito, marca como aprovado
        $this->form->fill([
            'status' => 'Aprovado',  // Ajusta para o campo correto e valor adequado
        ]);
    }
}


protected function haltCreation()
{
    // Cancela a criação silenciosamente sem lançar exceção
    // Exemplo: simplesmente não chama o parent create
    // Ou em alternativa lança uma validação que impede submissão
    // A forma exata depende da tua implementação
    throw new Halt(); // ou podes usar um return falso se suportar
}


//     public function onSolicitarTrocaClicado()
// {
//     logger('Botão de solicitar troca foi clicado.');
//     $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
// $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';
//      ScheduleRequest::create([
//             'id_schedule_conflict' => $this->conflictingSchedule->id,
//             'id_teacher_requester' => $teacher?->id,
//             'justification' => 'Conflito detetado automaticamente.', // Podes permitir edição depois
//             'status' => 'Pendente', // ou 'Troca Solicitada'
//         ]);

//        // Mostrar notificação com botões
//         Notification::make()
//             ->title('Conflito de horário detetado')
//             ->body("Já existe um agendamento para esta sala com $prof. Foi criado um pedido de troca pendente.")
//             ->warning()
//             ->persistent()
//             ->actions([
//                 NotificationAction::make('editarPedido')
//                     ->label('Editar pedido')
//                     ->button()
//                     ->color('primary'),
//                     // ->url(route('filament.resources.schedule-requests.edit', ['record' => $scheduleRequest->id])),

//                 NotificationAction::make('cancelar')
//                     ->label('Cancelar')
//                     ->button()
//                     ->color('secondary')
//                     ->close(),
//             ])
//             ->send();

//         throw new Halt('Erro ao criar agendamento devido a conflito.');

// }

public function onSolicitarTrocaClicado()
{
    logger('Botão de solicitar troca foi clicado.');
    $data = $this->form->getState();

    $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
    $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';
 $activeYear = SchoolYears::where('active', true)->first();
    // Atualizar o agendamento com conflito para estado "Pendente"
    //$this->conflictingSchedule->status = 'Pendente';
    //$this->conflictingSchedule->save();

    $schedule = Schedules::create([
        'id_room' => $this->conflictingSchedule->id_room,
        'id_weekday' => $this->conflictingSchedule->id_weekday,
        'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
        'id_teacher' => $teacher?->id,
        'id_subject' => $data['id_subject'],
        'turno' => $data['turno'],
        'id_schoolyear' => $activeYear?->id,
        'status' => 'Pendente', // ou outro estado apropriado
    ]);


    // Criar pedido de troca
    $scheduleRequest = ScheduleRequest::create([
        'id_schedule_conflict' => $this->conflictingSchedule->id,
        'id_teacher_requester' => $teacher?->id,
        'justification' => 'Conflito detetado automaticamente.',
        'status' => 'Pendente',
    ]);

    Notification::make()
        ->title('Pedido de troca criado')
        ->body("Foi criado um pedido de troca pendente para o conflito com $prof.")
        ->success()
        ->actions([
            NotificationAction::make('verPedido')
                ->label('Ver pedido')
                ->button()
                ->color('warning')
                // ->url(route('filament.resources.schedule-requests.edit', ['record' => $scheduleRequest->id])),
        ])
        ->send();

   //throw new Halt('Agendamento suspenso devido a pedido de troca pendente.');
}

}





