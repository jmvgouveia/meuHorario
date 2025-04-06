<?php

namespace App\Filament\Imports;

use App\Models\Room;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class RoomImporter extends Importer
{
    protected static ?string $model = Room::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('building_id')
                ->label('id do edificio')
                ->rules(['required', 'int']),
        ];
    }

    public function resolveRecord(): ?Room
    {
        return new Room();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'name' => $data['name'],
                'building_id' => $data['building_id'],
            ]);

            $record->save();

            $import->increment('processed_rows');
            $import->increment('successful_rows');
        } catch (\Exception $e) {
            $import->increment('processed_rows');
            $import->increment('failed_rows');

            throw $e;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Importados com sucesso {$count} Salas.";
    }
}
