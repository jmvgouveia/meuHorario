<?php

namespace App\Filament\Resources\TeacherSubjectsResource\Pages;

use App\Filament\Resources\TeacherSubjectsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherSubjects extends ListRecords
{
    protected static string $resource = TeacherSubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
