<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ScheduleRequest;
use App\Models\Schedules;
use App\Models\Teacher;
use App\Models\SchoolYears;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Mockery\Matcher\Not;


class ScheduleJustificationModal extends Component
{
    public $visible = false;
    public $conflictingScheduleId;
    public $justification = '';
    public $id_subject;
    public $turno;

    protected $rules = [
        'justification' => 'required|min:10',
        // regras para outros campos, se houver
    ];

    protected $listeners = ['openJustificationModal'];

    public function openJustificationModal($conflictingScheduleId)
    {
        $this->conflictingScheduleId = $conflictingScheduleId;
        $this->visible = true;
    }

    public function submit()
    {
        $this->validate();

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        $activeYear = SchoolYears::where('active', true)->first();

        $conflictingSchedule = Schedules::find($this->conflictingScheduleId);

        $schedule = Schedules::create([
            'id_room' => $conflictingSchedule->id_room,
            'id_weekday' => $conflictingSchedule->id_weekday,
            'id_timeperiod' => $conflictingSchedule->id_timeperiod,
            'id_teacher' => $teacher?->id,
            'id_subject' => $this->id_subject,
            'turno' => $this->turno,
            'id_schoolyear' => $activeYear?->id,
            'status' => 'Pendente',
        ]);

        ScheduleRequest::create([
            'id_schedule_conflict' => $this->conflictingScheduleId,
            'id_teacher_requester' => $teacher?->id,
            'id_schedule_novo' => $schedule->id,
            'justification' => $this->justification,
            'status' => 'Pendente',
        ]);

        $this->visible = false;
        $this->justification = '';

        // Filament::notify('success', 'Pedido de troca criado com sucesso!');


        // $currentRoom = $conflictingSchedule->room?->name ?? 'desconhecida';
        // $requester = $this->record->requester?->user;
        // $requesterName = $this->record->requester?->name ?? 'desconhecido';
        // $owner = $this->record->scheduleConflict?->teacher?->user;
        // $ownername = $owner?->name ?? 'desconhecido';

        // Notification::make()
        //     ->title("Pedido de troca criado com sucesso!")
        //     ->body("O seu pedido de troca foi enviado com sucesso para {$ownername} Horário: {$conflictingSchedule->weekday->name} às {$conflictingSchedule->timeperiod->name}.")
        //     ->success()
        //     ->sendToDatabase($requester);

        // // Notificação para o usuário que criou o pedido de troca

        // Notification::make()
        //     ->title('Pedido de troca criado')
        //     ->body('O seu pedido de troca foi enviado com sucesso.')
        //     ->success()
        //     ->send();
        // // Notificação para o professor que receberá o pedido de troca
        // // Envia notificação para o professor que receberá o pedido de troca

        // Notification::make()
        //     ->title('Pedido de troca Recebido')
        //     ->body("O utilizador {$requesterName} está a pedir para trocar a sua aula na sala {$currentRoom} por outra.")
        //     ->success()
        //     ->sendToDatabase($owner);




        $this->emit('refreshSchedulesTable'); // se quiseres refrescar lista na UI
    }

    public function render()
    {
        return view('livewire.schedule-justification-modal');
    }
}
