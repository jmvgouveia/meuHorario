<?php

namespace App\Observers;

use App\Models\ScheduleRequest;
use Mockery\Matcher\Not;
use Filament\Notifications\Notification;

class ScheduleRequestObserver
{
    /**
     * Handle the ScheduleRequest "created" event.
     */
    public function created(ScheduleRequest $scheduleRequest): void
    {
        // Força o carregamento das relações (evita lazy loading nulo)
        $scheduleRequest->loadMissing([
            'requester.user',
            'scheduleConflict.teacher.user',

        ]);

        $recepient = optional($scheduleRequest->scheduleConflict?->teacher)->user;

        // Só se houver recepient e teacher definidos
        if ($recepient && $scheduleRequest->teacher?->name) {
            Notification::make()
                ->title('Pedido de Troca de Horário')
                ->body("Recebeu um pedido de troca de horário do professor {$scheduleRequest->requester->name}.")
                ->sendToDatabase($recepient);
        } else {
            logger()->warning('Notificação não enviada. Dados incompletos no ScheduleRequest.', [
                'scheduleRequest_id' => $scheduleRequest->id,
                'has_teacher' => $scheduleRequest->relationLoaded('teacher'),
                'teacher' => $scheduleRequest->teacher,
                'recepient' => $recepient,
            ]);
        }
    }


    /**
     * Handle the ScheduleRequest "updated" event.
     */
    public function updated(ScheduleRequest $scheduleRequest): void
    {
        //
    }

    /**
     * Handle the ScheduleRequest "deleted" event.
     */
    public function deleted(ScheduleRequest $scheduleRequest): void
    {
        //
    }

    /**
     * Handle the ScheduleRequest "restored" event.
     */
    public function restored(ScheduleRequest $scheduleRequest): void
    {
        //
    }

    /**
     * Handle the ScheduleRequest "force deleted" event.
     */
    public function forceDeleted(ScheduleRequest $scheduleRequest): void
    {
        //
    }
}
