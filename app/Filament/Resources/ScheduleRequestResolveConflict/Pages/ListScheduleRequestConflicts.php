<?php

namespace App\Filament\Resources\ScheduleRequestResolveConflict\Pages;

use App\Filament\Resources\ScheduleRequestResolveConflict;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListScheduleRequestConflicts extends ListRecords
{
    protected static string $resource = ScheduleRequestResolveConflict::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->whereHas('scheduleConflict')
            ->where('status', '=', 'Escalado');
    }
}
