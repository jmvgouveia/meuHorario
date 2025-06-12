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
    public function submitJustification(array $data): void
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

        ScheduleRequest::create([
            'id_schedule_conflict' => $this->conflictingSchedule->id,
            'id_teacher_requester' => $teacher?->id,
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
}
