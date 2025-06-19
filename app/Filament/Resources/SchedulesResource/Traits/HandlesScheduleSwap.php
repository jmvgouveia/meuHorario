<?php

namespace App\Filament\Resources\SchedulesResource\Traits;

use App\Models\Teacher;
use App\Models\SchoolYears;
use App\Models\Schedules;
use App\Models\ScheduleRequest;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

trait HandlesScheduleSwap
{
    public function submitJustification(array $data)
    {
        $formState = $this->form->getState();

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        $activeYear = SchoolYears::where('active', true)->first();

        $schedule = Schedules::create([
            'id_room' => $this->conflictingSchedule->id_room,
            'id_weekday' => $this->conflictingSchedule->id_weekday,
            'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
            'id_teacher' => $teacher?->id,
            'id_subject' => $formState['id_subject'] ?? null,
            'turno' => $formState['turno'] ?? null,
            'id_schoolyear' => $activeYear?->id,
            'status' => 'Pendente',
        ]);

        $schedule->classes()->sync($formState['id_classes'] ?? []);
        $schedule->students()->sync($formState['students'] ?? []);

        $scheduleRequest = ScheduleRequest::create([
            'id_schedule_conflict' => $this->conflictingSchedule->id,
            'id_teacher_requester' => $teacher?->id,
            'id_schedule_novo' => $schedule->id,
            'justification' => $data['justification'] ?? 'Conflito detetado automaticamente.',
            'status' => 'Pendente',
        ]);

        $scheduleRequest->loadMissing('requester.user', 'scheduleConflict.teacher.user');
        $schedule->loadMissing('weekday', 'timeperiod', 'room');

        dd($schedule->toArray());

        //$scheduleRequest->loadMissing('requester.user', 'scheduleConflict.teacher.user');

        $requester = $scheduleRequest->requester?->user;
        $owner = $scheduleRequest->scheduleConflict?->teacher?->user;

        $currentRoom = $schedule?->room?->name ?? 'desconhecida';

        $dayName = $schedule->weekday?->name ?? 'desconhecido';
        $timePeriod = $schedule->timeperiod?->name ?? 'desconhecido';

        Notification::make()
            ->title("Pedido de Troca criado com sucesso!")
            ->body("O seu pedido de troca da sala: {$currentRoom} na {$dayName} as {$timePeriod} foi enviado com sucesso para {$owner?->name}.") //// ---
            ->persistent()
            ->success();

        Notification::make()
            ->title("{$requester?->name}")
            ->body("está a pedir para trocar a sala: {$currentRoom} na {$dayName} as {$timePeriod}.")
            ->success()
            ->sendToDatabase($owner);

        // 👇 Emite o evento Livewire para o browser redirecionar
        return redirect($this->getResource()::getUrl('index'));
    }
}
