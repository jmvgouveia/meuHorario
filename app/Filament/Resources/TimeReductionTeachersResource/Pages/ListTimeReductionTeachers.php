<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
