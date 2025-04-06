<?php

namespace App\Filament\Resources\TimePeriodResource\Pages;

use App\Filament\Resources\TimePeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimePeriod extends EditRecord
{
    protected static string $resource = TimePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 