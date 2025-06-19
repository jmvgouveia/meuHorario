<?php

namespace App\Filament\Resources\TimeReductionResource\Pages;

use App\Filament\Resources\TimeReductionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Teacher;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class CreateTimeReduction extends CreateRecord
{
    protected static string $resource = TimeReductionResource::class;
}
