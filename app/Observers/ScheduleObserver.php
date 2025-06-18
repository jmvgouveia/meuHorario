<?php

namespace App\Observers;

use App\Models\Schedules;

class ScheduleObserver
{
    /**
     * Handle the Schedules "created" event.
     */
    public function created(Schedules $schedules): void
    {
        //
    }

    /**
     * Handle the Schedules "updated" event.
     */
    public function updated(Schedules $schedules): void
    {
        //
    }

    /**
     * Handle the Schedules "deleted" event.
     */
    public function deleted(Schedules $schedules): void
    {
        //
    }

    /**
     * Handle the Schedules "restored" event.
     */
    public function restored(Schedules $schedules): void
    {
        //
    }

    /**
     * Handle the Schedules "force deleted" event.
     */
    public function forceDeleted(Schedules $schedules): void
    {
        //
    }
}
