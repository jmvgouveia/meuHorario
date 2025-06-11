<?php

namespace App\Filament\Resources\SchedulesResource\Pages;

use App\Filament\Resources\SchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SchoolYears;
use App\Models\Teacher;
use App\Models\Schedules;
use App\Models\ScheduleRequest;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
//
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Placeholder;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\Actions as ActionGroup;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\Log;



class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;

    protected $listeners = ['botaoSolicitarTrocaClicado' => 'onSolicitarTrocaClicado'];
    public ?string $justification = null;

    //  Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;
    use InteractsWithActions;

    //preencher automaticamente ano letivo e professor
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $activeYear = SchoolYears::where('active', true)->first();
        if ($activeYear) {
            $data['id_schoolyear'] = $activeYear->id;
        }

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        if ($teacher) {
            $data['id_teacher'] = $teacher->id;
        }

        $data['status'] = 'Aprovado';
        return $data;
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

    protected function beforeCreate(): void
    {

        $this->verificarConflitoEHorariosDisponiveis($this->data);
    }

    protected function beforeSave(): void
    {
        $this->verificarConflitoEHorariosDisponiveis($this->data, $this->record->id);
    }

    protected function afterCreate(): void
    {

        $this->afterSave();
        $this->hoursCounterUpdate($this->record);
    }

    protected function afterSave(): void
    {

        $record = $this->record;
        //     // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);
        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }

    protected function verificarConflitoEHorariosDisponiveis(array $data, ?int $ignoreId = null): void
    {
        $sala = \App\Models\Room::find($data['id_room']);
        $nomeSala = strtolower($sala->name ?? '');

        if ($nomeSala !== 'reunião') {
            $query = \App\Models\Schedules::with('teacher', 'room')
                ->where('id_room', $data['id_room'])
                ->where('id_weekday', $data['id_weekday'])
                ->where('id_timeperiod', $data['id_timeperiod']);

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            $this->conflictingSchedule = $query->first();

            if ($this->conflictingSchedule) {
                $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';

                Notification::make()
                    ->title('Conflito de horário detetado')
                    ->body("Já existe um agendamento para esta sala com $prof")
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Erro: Conflito de horário detetado.');
            }
        }

        // Validação da carga horária (igual à tua lógica existente, se necessário)

        // Validação da carga horária
        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();

        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacher->id)->first();

        $subject = \App\Models\Subject::find($data['id_subject']);

        $tipo = strtolower($subject->type ?? 'letiva');

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
    }

    protected function hoursCounterUpdate(Schedules $schedule): void
    {
        $teacherId = $schedule->id_teacher;
        $subject = $schedule->subject; // via relacionamento 'subject'

        if (!$teacherId || !$subject) {
            Log::warning('Teacher ou Subject não encontrados ao criar aula.');
            return;
        }

        // Exemplo: usar o campo "type" na tabela de disciplinas
        $tipo = strtolower($subject->type ?? 'letiva'); // Assume "letiva" por padrão

        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacherId)->first();

        if (!$counter) {
            Log::warning('TeacherHourCounter não encontrado.', ['id_teacher' => $teacherId]);
            return;
        }

        if ($tipo === 'nao letiva') {
            $counter->carga_componente_naoletiva = max(0, $counter->carga_componente_naoletiva - 1);
        } else {
            $counter->carga_componente_letiva = max(0, $counter->carga_componente_letiva - 1);
        }

        $counter->carga_horaria = $counter->carga_componente_letiva + $counter->carga_componente_naoletiva;
        $counter->save();

        Log::info('Carga horária atualizada após criação da aula.', [
            'teacher_id' => $teacherId,
            'tipo' => $tipo,
            'novo_total_letiva' => $counter->carga_componente_letiva,
            'novo_total_naoletiva' => $counter->carga_componente_naoletiva,
        ]);
    }
}
