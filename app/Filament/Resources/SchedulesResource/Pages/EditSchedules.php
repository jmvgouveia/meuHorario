<?php

namespace App\Filament\Resources\SchedulesResource\Pages;

use App\Filament\Resources\SchedulesResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\Schedules;
use App\Models\Teacher;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use App\Models\SchoolYears;
use App\Models\ScheduleRequest;


class EditSchedules extends EditRecord
{
    protected static string $resource = SchedulesResource::class;


    public ?Schedules $conflictingSchedule = null;


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se quiseres mudar algo antes de salvar os campos do modelo, faz aqui.
        return $data;
    }


    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $sala = \App\Models\Room::find($data['id_room']);
        $nomeSala = strtolower($sala->name ?? '');

        if ($nomeSala !== 'reunião') {
            $this->conflictingSchedule = Schedules::with('teacher', 'room')
                ->where('id_room', $data['id_room'])
                ->where('id_weekday', $data['id_weekday'])
                ->where('id_timeperiod', $data['id_timeperiod'])
                ->first();

            if ($this->conflictingSchedule) {
                logger('Conflito de horário detectado');

                $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';

                Notification::make()
                    ->title('Conflito de horário detetado')
                    ->body("Já existe um agendamento para esta sala com $prof")
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Erro ao criar agendamento. Conflito detectado.');
            }
        }

        // 🔎 Validação da carga horária
        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        logger('Professor encontrado', ['id_user' => Filament::auth()->id(), 'teacher' => $teacher]);

        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacher->id)->first();
        logger('Contador de horas encontrado', ['id_teacher' => $teacher->id, 'counter' => $counter]);

        $subject = \App\Models\Subject::find($data['id_subject']);
        logger('Disciplina encontrada', ['id_subject' => $data['id_subject'], 'subject' => $subject]);

        $tipo = strtolower($subject->type ?? 'letiva');
        logger('Tipo de disciplina', ['tipo' => $tipo]);

        if (!$counter) {
            Notification::make()
                ->title('Conflito de horário detetado')
                ->body("Contador de horas não encontrado para o professor")
                ->warning()
                ->persistent()
                ->send();

            throw new Halt('Contador de horas não encontrado para o professor.');
        }

        if ($tipo === 'nao letiva') {
            if ($counter->carga_componente_naoletiva <= 0) {
                Notification::make()
                    ->title('Sem horas disponíveis')
                    ->body('Sem horas disponíveis na componente **não letiva**.')
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Sem horas disponíveis na componente **não letiva**.');
            }
        } else {
            if ($counter->carga_componente_letiva <= 0) {
                Notification::make()
                    ->title('Sem horas disponíveis')
                    ->body('Sem horas disponíveis na componente **letiva**.')
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Sem horas disponíveis na componente **letiva**.');
            }
        }

        // Marca como aprovado
        $this->form->fill([
            'status' => 'Aprovado',
        ]);
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);

        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }
    public function submitJustification(array $data)
    {
        // Este é o estado do formulário principal (id_subject, turno, etc.)
        $formState = $this->form->getState();

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        $activeYear = SchoolYears::where('active', true)->first();

        $schedule = Schedules::create([
            'id_room' => $this->conflictingSchedule->id_room,
            'id_weekday' => $this->conflictingSchedule->id_weekday,
            'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
            'id_teacher' => $teacher?->id,
            logger('id_teacher', [$teacher?->id]),
            'id_subject' => $formState['id_subject'] ?? null,
            'turno' => $formState['turno'] ?? null,
            'id_schoolyear' => $activeYear?->id,
            'status' => 'Pendente',
        ]);

        // ➕ Associar turmas e alunos ao novo horário
        $schedule->classes()->sync($formState['id_classes'] ?? []);
        $schedule->students()->sync($formState['students'] ?? []);

        ScheduleRequest::create([
            'id_schedule_conflict' => $this->conflictingSchedule->id,
            'id_teacher_requester' => $teacher?->id,
            //   logger('id_teacher_requester',[$teacher?->id] ),
            // 'id_teacher_owner' => $this->conflictingSchedule->id_teacher,
            // logger('id_teacher_owner',[$this->conflictingSchedule->id_teacher] ),
            'id_schedule_novo' => $schedule->id,
            'justification' => $data['justification'] ?? 'Conflito detetado automaticamente.',
            'status' => 'Pendente',
        ]);

        Notification::make()
            ->title('Pedido de troca criado')
            ->body("O seu pedido de troca foi criado com sucesso para o conflito.")
            ->success()
            ->send();
    }
}
