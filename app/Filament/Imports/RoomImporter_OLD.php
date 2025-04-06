<?php

namespace App\Filament\Imports;

use App\Models\Room;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class RoomImporter extends Importer
{
    protected static ?string $model = Room::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->required()
                ->rules(['max:255']),
            ImportColumn::make('building_id')
                ->required()
                ->relationship(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Successfully imported {$count} rooms.";
    }
} 