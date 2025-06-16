<?php

namespace App\Filament\Imports;

use App\Models\Building;
use App\Models\Teacher;
use App\Models\TeacherPosition;
use App\Models\TimeReductionTeachers;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class TimeReductionTeacherImporter extends Importer
{
    protected static ?string $model = TimeReductionTeachers::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_teacher')
                ->label('Id do Professor')
                ->rules(['required', 'int']),
            ImportColumn::make('id_time_reduction')
                ->label('ID da Redução de Tempo')
                ->rules(['required', 'int']),
        ];
    }




    public function resolveRecord(): ?TimeReductionTeachers
    {
        return new TimeReductionTeachers();
    }

    // public function import(array $data, Import $import): void
    // {
    //     try {
    //         $record = $this->resolveRecord();

    //         if ($record === null) {
    //             return;
    //         }

    //         $record->fill([
    //             'id_teacher' => $data['id_teacher'],
    //             'id_time_reduction' => $data['id_time_reduction'],
    //         ]);

    //         $record->save();

    //         $import->increment('processed_rows');
    //         $import->increment('successful_rows');
    //     } catch (\Exception $e) {
    //         $import->increment('processed_rows');
    //         $import->increment('failed_rows');

    //         throw $e;
    //     }
    // }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Importados com sucesso {$count} Reducoes.";
    }
}
