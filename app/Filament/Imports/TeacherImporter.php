<?php

namespace App\Filament\Imports;

use App\Models\Teacher;
use App\Models\User;
use App\Models\TeacherHourCounter;
use App\Models\UserRoles;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('teachernumber')
                ->label('Número do Docente')
                ->rules(['required', 'string', 'max:20'])
                ->example('12345'),

            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255'])
                ->example('Jorge Batista Neves'),

            ImportColumn::make('acronym')
                ->label('Sigla')
                ->rules(['required', 'string', 'max:10'])
                ->example('JBN'),

            ImportColumn::make('birthdate')
                ->label('Data de Nascimento')
                ->rules(['required', 'date'])
                ->example('1983-05-12'),

            ImportColumn::make('startingdate')
                ->label('Data de Início')
                ->rules(['nullable', 'date'])
                ->example('2020-09-01'),

            ImportColumn::make('id_nationality')->label('Nacionalidade')->rules(['nullable', 'exists:nationalities,id']),
            ImportColumn::make('id_gender')->label('Género')->rules(['nullable', 'exists:genders,id']),
            ImportColumn::make('id_qualifications')->label('Qualificações')->rules(['nullable', 'exists:qualifications,id']),
            ImportColumn::make('id_department')->label('Departamento')->rules(['nullable', 'exists:departments,id']),
            ImportColumn::make('id_professionalrelationship')->label('Vínculo Profissional')->rules(['nullable', 'exists:professional_relationships,id']),
            ImportColumn::make('id_contractualrelationship')->label('Relação Contratual')->rules(['nullable', 'exists:contractual_relationships,id']),
            ImportColumn::make('id_salaryscale')->label('Escalão Salarial')->rules(['nullable', 'exists:salary_scales,id']),

            ImportColumn::make('email')
                ->label('Email do Professor (Institucional)')
                ->rules(['required', 'email', 'distinct'])
                ->fillRecordUsing(function () {
                    // Retorna null para não preencher nada no modelo
                    return null;
                }) // não será usado no modelo Teacher
                ->example('jorge.neves@edu.madeira.gov.pt'),
        ];
    }

    public function resolveRecord(): ?Teacher
    {
        try {
            $data = $this->data;

            Log::debug('Importando professor:', $data);

            $name = trim($data['name']);
            $email = trim($data['email']);
            $sigla = trim($data['acronym']);
            $birthdate = $data['birthdate'];

            if (!$birthdate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                Notification::make()
                    ->title('Erro ao importar professor')
                    ->body("Data de nascimento inválida para {$name}.")
                    ->danger()
                    ->persistent()
                    ->send();
                return null;
            }

            if (User::where('email', $email)->exists()) {
                Notification::make()
                    ->title('Email duplicado')
                    ->body("O email {$email} já está em uso.")
                    ->danger()
                    ->persistent()
                    ->send();
                return null;
            }

            // Gerar password: SIGLA + DIA + MÊS + &
            $dateParts = explode('-', $birthdate);
            $day = str_pad($dateParts[2], 2, '0', STR_PAD_LEFT);
            $month = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
            $rawPassword = $sigla . $day . $month . '&';

            // Criar o utilizador
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($rawPassword),

            ]);

            // 👇 Atribuir o role 'professor'
            //            $user->assignRole('professor');    //--- ROLES


            // Preparar dados para Teacher
            $teacherData = [
                'teachernumber' => $data['teachernumber'],
                'name' => $name,
                'acronym' => $sigla,
                'birthdate' => $birthdate,
                'startingdate' => $data['startingdate'] ?? null,
                'id_nationality' => $data['id_nationality'] ?? null,
                'id_gender' => $data['id_gender'] ?? null,
                'id_qualifications' => $data['id_qualifications'] ?? null,
                'id_department' => $data['id_department'] ?? null,
                'id_professionalrelationship' => $data['id_professionalrelationship'] ?? null,
                'id_contractualrelationship' => $data['id_contractualrelationship'] ?? null,
                'id_salaryscale' => $data['id_salaryscale'] ?? null,
                'id_user' => $user->id,
            ];

            // Criar o professor
            $teacher = Teacher::create($teacherData);

            // Criar o contador de horas
            TeacherHourCounter::create([
                'id_teacher' => $teacher->id,
                'carga_horaria' => 26,
                'carga_componente_letiva' => 22,
                'carga_componente_naoletiva' => 4,
            ]);

            Log::info('Professor importado com sucesso:', ['id' => $teacher->id, 'email' => $email]);

            return $teacher;
        } catch (\Exception $e) {
            Log::error('Erro ao importar professor:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'linha' => $this->data ?? [],
            ]);

            Notification::make()
                ->title('Erro inesperado')
                ->body('Ocorreu um erro ao importar este registo.')
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }

    protected function handleRecordCreation(array $data): Teacher
    {
        return $this->record; // resolveRecord trata de tudo
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "{$count} professores importados com sucesso.";
    }
}
