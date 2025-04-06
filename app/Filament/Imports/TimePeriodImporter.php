<?php

namespace App\Filament\Imports;

use App\Models\TimePeriod;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TimePeriodImporter extends Importer
{
    protected static ?string $model = TimePeriod::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('description')
                ->label('Description')
                ->rules(['string','max:255']),
        ];
    }

    public function resolveRecord(): ?TimePeriod
    {
        return new TimePeriod();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'description' => $data['description'],

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
        return "Successfully imported {$count} time periods.";
    }
}
