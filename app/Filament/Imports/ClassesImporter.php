<?php

namespace App\Filament\Imports;

use App\Models\Classes;
use App\Models\Subject;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class ClassesImporter extends Importer
{
    protected static ?string $model = Classes::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('class')
                ->label('Nome da Turma')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'int']),
        ];
    }

    public function resolveRecord(): ?Classes
    {
        return new Classes();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'class' => $data['class'],
                'id_course' => $data['id_course'],
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
        return "{$count}  Trumas importadas com sucesso.";
    }
}
