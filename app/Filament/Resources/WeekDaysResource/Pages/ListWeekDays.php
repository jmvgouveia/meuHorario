<?php

namespace App\Filament\Resources\WeekDaysResource\Pages;

use App\Filament\Resources\WeekDaysResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeekDays extends ListRecords
{
    protected static string $resource = WeekDaysResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
