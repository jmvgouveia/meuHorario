<?php

namespace App\Filament\Resources\SchoolYearsResource\Pages;

use App\Filament\Resources\SchoolYearsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolYears extends EditRecord
{
    protected static string $resource = SchoolYearsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
