<?php

namespace App\Filament\Resources\CourseSubjectsResource\Pages;

use App\Filament\Resources\CourseSubjectsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseSubjects extends ListRecords
{
    protected static string $resource = CourseSubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
