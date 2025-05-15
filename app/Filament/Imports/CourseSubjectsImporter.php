<?php

namespace App\Filament\Imports;

use App\Models\CourseSubjects;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class CourseSubjectsImporter extends Importer
{
    protected static ?string $model = CourseSubjects::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_subject')
                ->label('ID da Disciplina')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Escolar')
                ->rules(['required', 'string', 'max:255']),

        ];
    }

    public function resolveRecord(): ?CourseSubjects
    {
        return new CourseSubjects();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'id_subject' => $data['id_subject'],
                'id_course' => $data['id_course'],
                'id_schoolyear' => $data['id_schoolyear'],
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
        return "{$count} Relacos importadas com sucesso.";
    }
}
