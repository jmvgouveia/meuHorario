<?php

namespace App\Filament\Resources\TeacherSubjectsResource\Pages;

use App\Filament\Resources\TeacherSubjectsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherSubjects extends EditRecord
{
    protected static string $resource = TeacherSubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
