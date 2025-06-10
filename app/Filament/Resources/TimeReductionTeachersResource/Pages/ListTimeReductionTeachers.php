<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\TeacherHourCounter;
use Filament\Tables\Actions\DeleteAction;

class ListTimeReductionTeachers extends ListRecords
{
    protected static string $resource = TimeReductionTeachersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
