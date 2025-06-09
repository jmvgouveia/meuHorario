<?php

namespace App\Filament\Resources\TeacherPositionResource\Pages;

use App\Filament\Resources\TeacherPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TeacherHourCounter;
use App\Models\TeacherPosition;

class CreateTeacherPosition extends CreateRecord
{
     protected static string $resource = TeacherPositionResource::class;
//     protected function afterCreate(): void
// {
//     $record = $this->record;
//     $teacher = $record->teacher->load('teacherPositions.position');

// //    $teacher = $record->teacher;

//     // Soma total das reduções letiva e não letiva de todos os cargos do professor
//     $reducaoLetivaTotal = $teacher->teacherPositions->sum(fn ($pos) => $pos->position->reducao_letiva ?? 0);
//     $reducaoNaoLetivaTotal = $teacher->teacherPositions->sum(fn ($pos) => $pos->position->reducao_naoletiva ?? 0);

//     // Carga base
//     $baseLetiva = 22;
//     $baseNaoLetiva = 0;

//     $novaLetiva = max(0, $baseLetiva - $reducaoLetivaTotal);
//     $novaNaoLetiva = max(0, $baseNaoLetiva - $reducaoNaoLetivaTotal);

//     TeacherHourCounter::updateOrCreate(
//         ['id_teacher' => $teacher->id],
//         [
//             'carga_horaria' => $baseLetiva + $baseNaoLetiva,
//             'carga_componente_letiva' => $novaLetiva,
//             'carga_componente_naoletiva' => $novaNaoLetiva,
//             'autorizado_horas_extra' => 'nao_autorizado',
//         ]
//     );
// }
}
