<?php

namespace App\Filament\Resources\CourseSubjectsResource\Pages;

use App\Filament\Resources\CourseSubjectsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSubjects extends EditRecord
{
    protected static string $resource = CourseSubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
