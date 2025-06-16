<?php

namespace App\Filament\Imports;

use App\Models\Building;
use App\Models\Teacher;
use App\Models\TeacherPosition;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class TeacherPositionImporter extends Importer
{
    protected static ?string $model = TeacherPosition::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_teacher')
                ->label('Id do Professor')
                ->rules(['required', 'int']),
            ImportColumn::make('id_position')
                ->label('ID do Cargo')
                ->rules(['required', 'int']),
        ];
    }

    public function resolveRecord(): ?TeacherPosition
    {
        return new TeacherPosition();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Importados com sucesso {$count} edifícios.";
    }
}
