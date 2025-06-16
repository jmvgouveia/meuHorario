<?php

namespace App\Filament\Imports;

use App\Models\Registration;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationImporter extends Importer
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        Log::debug('RegistrationImporter getColumns called');
        return [
            ImportColumn::make('id_student')
                ->label('ID do Aluno')
                ->rules(['required', 'integer']),
            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'integer']),
            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Letivo')
                ->rules(['required', 'integer']),
            ImportColumn::make('id_class')
                ->label('ID da Turma')
                ->rules(['required', 'integer']),
            // Campo que será completamente ignorado pelo Filament
            ImportColumn::make('id_subjects')
                ->label('ID da(s) Disciplina(s)')
                ->rules(['required', 'string'])
                ->example('1,2,3')
                ->fillRecordUsing(function () {
                    // Retorna null para não preencher nada no modelo
                    return null;
                }),
        ];
    }

    // Override completo do processo de criação
    public function resolveRecord(): ?Registration
    {
        try {
            // Dados da linha atual
            $rowData = $this->data;

            Log::debug('Processando linha:', $rowData);

            // Dados APENAS para criar o Registration (campos que existem na tabela)
            $registrationData = [
                'id_student' => (int) $rowData['id_student'],
                'id_course' => (int) $rowData['id_course'],
                'id_schoolyear' => (int) $rowData['id_schoolyear'],
                'id_class' => (int) $rowData['id_class'],
            ];

            // Cria o registro de Registration
            $registration = Registration::create($registrationData);

            Log::debug('Registration criado com ID:', ['id' => $registration->id]);

            // Processa as disciplinas se existirem
            if (isset($rowData['id_subjects']) && !empty($rowData['id_subjects'])) {
                $this->attachSubjectsToRegistration($registration, $rowData['id_subjects']);
            }

            return $registration;
        } catch (\Exception $e) {
            Log::error('Erro ao processar registration:', [
                'error' => $e->getMessage(),
                'data' => $this->data ?? 'N/A'
            ]);
            throw $e;
        }
    }

    private function attachSubjectsToRegistration(Registration $registration, string $subjectsList): void
    {
        try {
            Log::debug('Iniciando anexação de subjects:', [
                'registration_id' => $registration->id,
                'subjects_string' => $subjectsList
            ]);

            // Converte string "136,98,130" em array [136,98,130]
            $subjectIds = array_map('trim', explode(',', $subjectsList));
            $subjectIds = array_filter($subjectIds, function ($id) {
                return is_numeric($id) && $id > 0;
            });
            $subjectIds = array_map('intval', $subjectIds);

            Log::debug('IDs processados:', [
                'registration_id' => $registration->id,
                'subject_ids' => $subjectIds,
                'count' => count($subjectIds)
            ]);

            if (!empty($subjectIds)) {
                // Anexa as disciplinas na tabela pivot
                $registration->subjects()->attach($subjectIds);

                Log::debug('Subjects anexados com sucesso:', [
                    'registration_id' => $registration->id,
                    'attached_subjects' => $subjectIds
                ]);

                // Verifica se realmente foram anexados
                $attachedCount = $registration->subjects()->count();
                Log::debug('Verificação após anexação:', [
                    'registration_id' => $registration->id,
                    'total_subjects_attached' => $attachedCount
                ]);
            } else {
                Log::warning('Nenhum subject ID válido encontrado:', [
                    'registration_id' => $registration->id,
                    'original_string' => $subjectsList
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao anexar subjects:', [
                'registration_id' => $registration->id,
                'subjects_list' => $subjectsList,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // Impede que o Filament tente fazer qualquer operação adicional
    protected function handleRecordCreation(array $data): Registration
    {
        // Não faz nada - o resolveRecord() já cuidou de tudo
        return $this->record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "{$count} Matriculas Importadas com sucesso.";
    }
}
