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
use App\Filament\Resources\SchedulesResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\SchedulesResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\SchedulesResource\Traits\HandlesScheduleSwap;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Illuminate\Console\Scheduling\Schedule;
use Mockery\Matcher\Not;

class EditSchedules extends EditRecord
{
    protected static string $resource = SchedulesResource::class;

    public ?Schedules $conflictingSchedule = null;
    use CheckScheduleWindow;
    use ChecksScheduleConflicts;
    use HandlesScheduleSwap;




    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se quiseres mudar algo antes de salvar os campos do modelo, faz aqui.
        return $data;
    }


    protected function beforeSave(): void
    {

        $this->validateScheduleWindow();

        $this->checkScheduleConflictsAndAvailability($this->data, $this->record?->id);


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

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),


            DeleteAction::make()
                ->label('Eliminar Horário')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {

                    $this->validateScheduleWindow();

                    SchedulesResource::rollbackScheduleRequest($this->record);

                    if ($this->record->status !== 'Pendente') {

                        SchedulesResource::hoursCounterUpdate($this->record, true);
                    }

                    $this->record->delete();

                    Notification::make()
                        ->title("Horário Eliminado")
                        ->body("O horário com ID: {$this->record->id} foi eliminado com sucesso.")
                        ->success()
                        ->sendToDatabase(Filament::auth()->user());

                    Notification::make()
                        ->title('Horário Eliminado')
                        ->body("O horário com ID: {$this->record->id} foi eliminado com sucesso.")
                        ->success()
                        ->send();
                    $this->redirect(filament()->getUrl()); // 👈 redireciona para o "main"
                }),



            $this->getCancelFormAction(), // Botão "Cancelar"
        ];
    }

    protected function getRecordActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('justificarConflito')
                    ->label('Solicitar Troca de Horário')
                    ->visible(fn($livewire) => $livewire->conflictingSchedule !== null)
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->modalHeading('Justificação do Conflito')
                    ->modalSubmitActionLabel('Submeter Justificação')
                    ->modalCancelActionLabel('Cancelar')
                    ->form([
                        Textarea::make('justification')
                            ->label('Escreva a justificação')
                            ->helperText('Escreva uma justificação para o conflito de horário. Esta justificação será enviada ao professor responsável pelo horário em conflito.')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(function (array $data, $livewire) {
                        $livewire->submitJustification($data);
                        //return redirect()->route('filament.admin.pages.dashboard');
                    })
            ]),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return SchedulesResource::getUrl();
    }
}
