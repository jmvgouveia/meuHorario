<?php

namespace App\Filament\Resources\PositionResource\Pages;

use App\Filament\Resources\PositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Teacher;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;


class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;
}
