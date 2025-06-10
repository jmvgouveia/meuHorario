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

        return $data;
    }

    protected function beforeCreate(): void
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

    public function getFormSchema(): array
    {

        return [
            // ... outros campos do formulário

            Forms\Components\Actions::make([
                Action::make('justificarConflito')
                    ->label('Justificar Conflito')
                    ->visible(fn() => $this->conflictingSchedule !== null)
                    ->modalHeading('Justificação do Conflito')
                    ->modalSubmitActionLabel('Submeter Justificação')
                    ->modalCancelActionLabel('Cancelar')
                    ->form([
                        Textarea::make('justification')
                            ->label('Escreva a justificação')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(fn(array $data) => $this->submitJustification($data)),
            ]),
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $teacherId = $record->id_teacher;
        $subject = $record->subject; // via relacionamento 'subject'

        if (!$teacherId || !$subject) {
            Log::warning('Teacher ou Subject não encontrados ao criar aula.');
            return;
        }

        // Exemplo: usar o campo "tipo" na tabela de disciplinas
        $tipo = strtolower($subject->type ?? 'letiva'); // Assume "letiva" por padrão
        // dd($tipo);
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
