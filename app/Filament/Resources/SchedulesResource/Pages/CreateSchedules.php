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
        SchedulesResource::hoursCounterUpdate($this->record, false);
        // $this->hoursCounterUpdate($this->record);
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


    protected function getRedirectUrl(): string
    {
        return SchedulesResource::getUrl();
    }
}
