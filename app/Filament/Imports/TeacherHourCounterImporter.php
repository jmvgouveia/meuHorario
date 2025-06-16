<?php

namespace App\Filament\Imports;

use App\Models\Building;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\TeacherPosition;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class TeacherHourCounterImporter extends Importer
{
    protected static ?string $model = TeacherHourCounter::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_teacher')
                ->label('Id do Professor')
                ->rules(['required', 'int']),
            ImportColumn::make('carga_horaria')
                ->label('Carga Horária')
                ->rules(['required', 'int']),
            ImportColumn::make('carga_componente_letiva')
                ->label('Carga Componente Letiva')
                ->rules(['required', 'int']),
            ImportColumn::make('carga_componente_naoletiva')
                ->label('Carga Componente Não Letiva')
                ->rules(['required', 'int']),
            ImportColumn::make('autorizado_horas_extra')
                ->label('Autorizado Horas Extra')
                ->rules(['required', 'ENUM:Autorizado,Nao_Autorizado']),
        ];
    }

    public function resolveRecord(): ?TeacherHourCounter
    {
        return new TeacherHourCounter();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "{$count} Horas Importadas com sucesso.";
    }
}
