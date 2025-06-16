<?php

namespace App\Filament\Imports;

use App\Models\Building;
use App\Models\Teacher;
use App\Models\TeacherPosition;
use App\Models\TeacherSubjects;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class TeacherSubjectsImporter extends Importer
{
    protected static ?string $model = TeacherSubjects::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_teacher')
                ->label('Id do Professor')
                ->rules(['required', 'int']),
            ImportColumn::make('id_subject')
                ->label('ID do Cargo')
                ->rules(['required', 'int']),
            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Letivo')
                ->rules(['required', 'int']),
        ];
    }

    public function resolveRecord(): ?TeacherSubjects
    {
        return new TeacherSubjects();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "{$count}  Associacoes feitas entre Professor e Disciplina.";
    }
}
