<?php

// namespace App\Filament\Imports;

// use App\Models\Registration;
// use App\Models\Student;
// use App\Models\Course;
// use App\Models\Classes;
// use App\Models\SchoolYears;
// use App\Models\Subject;
// use Filament\Actions\Imports\ImportColumn;
// use Filament\Actions\Imports\Importer;
// use Filament\Actions\Imports\Models\Import;
// use Illuminate\Support\Facades\Log;

// class RegistrationImporter extends Importer
// {
//     protected static ?string $model = Registration::class;

//     public static function getColumns(): array
//     {
//         return [
//             ImportColumn::make('id_student'),
//             ImportColumn::make('id_course'),
//             ImportColumn::make('id_class'),
//             ImportColumn::make('id_schoolyear'),
//             ImportColumn::make('subjects')
//                 ->label('Disciplinas (IDs separados por vírgula)')
//                 ->rules(['nullable', 'string']),
//         ];
//     }

//     public static function getRows(array $data, Import $import): array
//     {
//         unset($data['id']);

//         return [$data];
//     }

//     public static function getOptionsFormComponents(): array
//     {
//         return [];
//     }

//     public function import(array $data, Import $import): void
//     {
//         Log::info('IMPORT DATA RECEBIDA', $data);

//         try {
//             $import->increment('processed_rows');

//             // Validação
//             if (
//                 !Student::find($data['id_student']) ||
//                 !Course::find($data['id_course']) ||
//                 !Classes::find($data['id_class']) ||
//                 !SchoolYears::find($data['id_schoolyear'])
//             ) {
//                 Log::warning("IDs inválidos: " . json_encode($data));
//                 $import->increment('failed_rows');
//                 return;
//             }

//             // Registo ou atualização
//             $registration = Registration::updateOrCreate(
//                 [
//                     'id_student' => $data['id_student'],
//                     'id_schoolyear' => $data['id_schoolyear'],
//                 ],
//                 [
//                     'id_course' => $data['id_course'],
//                     'id_class' => $data['id_class'],
//                 ]
//             );

//             Log::info('MATRÍCULA CRIADA ID: ' . $registration->id);

//             // Disciplinas
//             if (!empty($data['subjects'])) {
//                 $subjectIds = collect(explode(',', $data['subjects']))
//                     ->map(fn($id) => (int) trim($id))
//                     ->filter(fn($id) => Subject::find($id));

//                 Log::info('ASSOCIAR SUBJECTS: ' . json_encode($subjectIds));

//                 $registration->subjects()->sync($subjectIds);
//             }

//             $import->increment('successful_rows');
//         } catch (\Throwable $e) {
//             $import->increment('failed_rows');
//             Log::error("ERRO GERAL: " . $e->getMessage());
//             throw $e;
//         }
//     }

//     public static function getCompletedNotificationBody(Import $import): string
//     {
//         return "Importadas com sucesso {$import->successful_rows} matrículas.";
//     }
// }
