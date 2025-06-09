<?php

namespace App\Filament\Resources\TimeReductionTeachersResource\Pages;

use App\Filament\Resources\TimeReductionTeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeReductionTeachers extends EditRecord
{
    protected static string $resource = TimeReductionTeachersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
