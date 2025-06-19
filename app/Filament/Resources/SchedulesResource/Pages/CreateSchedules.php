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
use Carbon\Carbon;
use App\Filament\Resources\SchedulesResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\SchedulesResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\SchedulesResource\Traits\HandlesScheduleSwap;
use Filament\Actions\CreateAction;





class CreateSchedules extends CreateRecord

{
    protected static string $resource = SchedulesResource::class;

    use InteractsWithActions;
    use CheckScheduleWindow;
    use ChecksScheduleConflicts;
    use HandlesScheduleSwap;

    protected $listeners = ['botaoSolicitarTrocaClicado' => 'onSolicitarTrocaClicado'];
    public ?string $justification = null;

    //  Armazena estado interno da página
    public ?Schedules $conflictingSchedule = null;


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

    protected function beforeCreate(): void
    {
        $this->validateScheduleWindow();



        $this->checkScheduleConflictsAndAvailability($this->data);
    }



    protected function beforeSave(): void
    {
        $this->validateScheduleWindow();
        $this->checkScheduleConflictsAndAvailability($this->data, $this->record->id);
    }

    protected function afterCreate(): void
    {
        $this->afterSave();
        $this->hoursCounterUpdate($this->record);
    }


    public function mount(): void
    {
        $this->form->fill([
            'id_weekday' => request('weekday'),
            'id_timeperiod' => request('timeperiod'),
        ]);
    }

    protected function afterSave(): void
    {

        $record = $this->record;
        //     // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);
        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }




    protected function hoursCounterUpdate(Schedules $schedule): void
    {
        $teacherId = $schedule->id_teacher;
        $subject = $schedule->subject; // via relacionamento 'subject'

        if (!$teacherId || !$subject) {
            // Log::warning('Teacher ou Subject não encontrados ao criar aula.');
            return;
        }

        // Exemplo: usar o campo "type" na tabela de disciplinas
        $tipo = strtolower($subject->type ?? 'letiva'); // Assume "letiva" por padrão

        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $teacherId)->first();

        if (!$counter) {
            // Log::warning('TeacherHourCounter não encontrado.', ['id_teacher' => $teacherId]);
            return;
        }

        if ($tipo === 'nao letiva') {
            $counter->carga_componente_naoletiva = max(0, $counter->carga_componente_naoletiva - 1);
        } else {
            $counter->carga_componente_letiva = max(0, $counter->carga_componente_letiva - 1);
        }

        $counter->carga_horaria = $counter->carga_componente_letiva + $counter->carga_componente_naoletiva;
        $counter->save();

        // Log::info('Carga horária atualizada após criação da aula.', [
        //     'teacher_id' => $teacherId,
        //     'tipo' => $tipo,
        //     'novo_total_letiva' => $counter->carga_componente_letiva,
        //     'novo_total_naoletiva' => $counter->carga_componente_naoletiva,
        // ]);
    }

    protected function getRedirectUrl(): string
    {
        return SchedulesResource::getUrl();
    }
}
