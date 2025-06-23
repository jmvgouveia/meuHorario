<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
//use Filament\Actions\CancelAction;
use Filament\Pages\Actions\CancelAction;
use Filament\Forms\Components\Select;
use App\Models\Room;
use App\Models\User;
use Filament\Facades\Filament;  // <-- Importar aqui
use App\Filament\Resources\SchedulesResource;
use Filament\Actions\DeleteAction;

class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;
    use \App\Filament\Resources\SchedulesResource\Traits\CheckScheduleWindow;


    protected function getFormActions(): array
    {



        $this->record->loadMissing('scheduleConflict');

        $teacherId = Filament::auth()->user()?->teacher?->id;

        $requesterId = $this->record->id_teacher_requester;
        $conflictOwnerId = $this->record->scheduleConflict?->id_teacher;

        $isRequestOwner = $teacherId === $requesterId;
        $isReceiver = $teacherId === $conflictOwnerId;

        $isGestor = in_array(Filament::auth()->id(), [1]); // podes trocar por ->hasRole('admin')


        $status = $this->record->status;

        $actions = [];

        // ✅ Quem pode aprovar ou recusar (dono do horário original)
        if ($isReceiver || $isGestor) {

            if ($status !== 'Recusado') {
                $actions[] = Action::make('accept')
                    ->label('Aceitar Troca')
                    ->color('success')
                    ->form([
                        Select::make('id_room_novo')
                            ->label('Sala Nova')
                            ->placeholder('Selecione uma sala disponível...')
                            ->required()
                            ->preload()
                            ->options(fn() => $this->getAvailableRooms()),

                        Textarea::make('response')
                            ->label($isGestor ? 'Justificação da Aprovação (Gestor)' : 'Justificação da Aceitação')
                            ->placeholder('Descreva a razão da aprovação da troca...')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data) use ($isGestor) {
                        // dados para a notificação

                        $schedule = $this->record->scheduleConflict;
                        $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                        $salaNova = \App\Models\Room::find($data['id_room_novo'])?->name ?? 'desconhecida';

                        $requester = $this->record->requester?->user;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $owner = $this->record->scheduleConflict?->teacher?->user;
                        $ownername = $owner?->name ?? 'desconhecido';
                        $dayName = $this->record->scheduleConflict?->weekday?->weekday ?? 'desconhecido';
                        $timePeriod = $this->record->scheduleConflict?->timeperiod?->description ?? 'desconhecido';
                        $currentRoom = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';


                        if ($isGestor) {
                            $this->record->update([
                                'status' => 'Aprovado DP',
                                'response' => $data['response'],
                            ]);

                            $this->record->scheduleConflict?->update([
                                'status' => 'Aprovado DP',
                                'id_room' => $data['id_room_novo'],
                                'responded_at' => now(),
                            ]);
                        } else {
                            $this->validateScheduleWindow();
                            $this->record->update([
                                'status' => 'Aprovado',
                                'response' => $data['response'],
                            ]);

                            $this->record->scheduleConflict?->update([
                                'status' => 'Aprovado',
                                'id_room' => $data['id_room_novo'],
                                'responded_at' => now(),
                            ]);
                        }


                        $this->record->scheduleNovo?->update(['status' => 'Aprovado']);


                        Notification::make()
                            ->title('Pedido de troca aprovado com sucesso')
                            ->body("Aprovou o pedido de {$requestername} para trocar a sala {$currentRoom}, no {$dayName} às {$timePeriod}. A nova sala será {$salaNova}")
                            ->success()
                            ->send();

                        Notification::make()
                            ->title("Pedido de troca aprovado")
                            ->body("O(a) professor(a) {$ownername} aprovou o seu pedido de trocar a sala {$currentRoom}, na {$dayName} às {$timePeriod}.")
                            ->success()
                            ->sendToDatabase($requester); // Envia e armazena no banco de dados

                        SchedulesResource::hoursCounterUpdate($this->record->scheduleNovo, false);

                        return redirect($this->getResource()::getUrl('index'));
                    });
            }
            // Se não está aprovado, pode recusar
            // (não pode recusar se já foi aprovado)
            if ($status !== 'Aprovado') {
                $actions[] = Action::make('reject')
                    ->label('Recusar Troca')
                    ->color('danger')
                    ->form([
                        Textarea::make('response')
                            ->label('Justificação para Recusa')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data) use ($isGestor) {

                        if ($isGestor) {
                            $this->record->update([
                                'status' => 'Recusado DP',
                                'response' => $data['response'],
                                'responded_at' => now(),
                            ]);
                        } else {
                            $this->validateScheduleWindow();
                            $this->record->update([
                                'status' => 'Recusado',
                                'response' => $data['response'],
                                'responded_at' => now(),
                            ]);
                        };


                        $schedule = $this->record->scheduleConflict;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                        $requester = $this->record->requester?->user;
                        $ownername = $this->record->scheduleConflict?->teacher?->name ?? 'desconhecido';
                        $owner = $this->record->scheduleConflict?->teacher?->user;
                        $dayName = $this->record->scheduleConflict?->weekday?->weekday ?? 'desconhecido';
                        $timePeriod = $this->record->scheduleConflict?->timeperiod?->description ?? 'desconhecido';
                        $currentRoom = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';

                        Notification::make()
                            ->title("Pedido de troca recusado com sucesso")
                            ->body("Recusou o pedido de {$requestername} para a troca da aula na sala {$currentRoom}, agendada para {$dayName} às {$timePeriod}.")
                            ->danger()
                            ->send();

                        Notification::make()
                            ->title("Pedido de troca recusado")
                            ->body("O professor {$ownername} recusou a troca da aula na sala {$currentRoom}, prevista para {$dayName} às {$timePeriod}.")
                            ->danger()
                            ->sendToDatabase($requester);


                        return redirect($this->getResource()::getUrl('index'));
                    });
            }
        }

        // ✅ Quem fez o pedido pode responder (se ainda não foi aprovado ou recusado)
        if (($isRequestOwner || $isGestor)  && $status === 'Recusado') {
            $actions[] = Action::make('escalar')
                ->label('Escalar Situação')
                ->color('warning')
                ->form([
                    Textarea::make('justification_escalada')
                        ->label('Justificação para Escalar')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->validateScheduleWindow();
                    $this->record->update([
                        'status' => 'Escalado',
                        'justification_escalada' => $data['justification_escalada'],
                        'justification_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Situação Escalada')
                        ->warning()
                        ->body('O pedido foi escalado para análise.')
                        ->send();

                    $schedule = $this->record->scheduleConflict;
                    $idMarcacao = $schedule?->id ?? 'N/A';
                    $requester = $this->record->requester?->user;
                    $requestername = $this->record->requester?->name ?? 'desconhecido';
                    $owner = $this->record->scheduleConflict?->teacher?->user;
                    $ownername = $owner?->name ?? 'desconhecido';
                    $dayName = $this->record->scheduleConflict?->weekday?->weekday ?? 'desconhecido';
                    $timePeriod = $this->record->scheduleConflict?->timeperiod?->description ?? 'desconhecido';
                    $currentRoom = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';


                    Notification::make()
                        ->title('Pedido Escalado')
                        ->warning()
                        ->body("O pedido de troca com o professor {$ownername}, referente à aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}, foi escalado para análise superior.")
                        ->send();

                    // Notifica o dono do horário original
                    Notification::make()
                        ->title("Pedido de troca escalado")
                        ->body("O professor {$requestername} escalou o pedido de troca da aula na sala {$currentRoom}, marcada para {$dayName} às {$timePeriod}.")
                        ->warning()
                        ->sendToDatabase($owner);

                    Notification::make()
                        ->title("Pedido de troca escalado")
                        ->body("O pedido de troca com o professor {$ownername}, referente à aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}, foi escalado para análise superior.")
                        ->warning()
                        ->sendToDatabase($requester);



                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // ✅ Quem fez o pedido pode cancelar (se ainda estiver pendente)
        if (($isRequestOwner || $isGestor) && $status === 'Pendente') {
            $actions[] = Action::make('cancelRequest')
                ->label('Cancelar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar este pedido de troca?')
                ->action(function () {
                    $this->validateScheduleWindow();
                    $this->record->update(['status' => 'Cancelado']);
                    $this->record->scheduleNovo?->update(['status' => 'Cancelado']);


                    $requester = $this->record->requester?->user;
                    $requestername = $this->record->requester?->name ?? 'desconhecido';
                    $owner = $this->record->scheduleConflict?->teacher?->user;
                    $ownername = $owner?->name ?? 'desconhecido';
                    $dayName = $this->record->scheduleConflict?->weekday?->weekday ?? 'desconhecido';
                    $timePeriod = $this->record->scheduleConflict?->timeperiod?->description ?? 'desconhecido';
                    $currentRoom = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';


                    Notification::make()
                        ->title("Pedido de troca cancelado")
                        ->body("Cancelou o pedido de troca com o professor {$ownername}, referente à aula na sala {$currentRoom} ({$dayName}, {$timePeriod}).")
                        ->success()
                        ->send();

                    Notification::make()
                        ->title("Pedido de troca cancelado")
                        ->body("O professor {$requestername} cancelou o pedido referente à aula na sala {$currentRoom}, agendada para {$dayName} às {$timePeriod}.")
                        ->success()
                        ->sendToDatabase($owner); // Envia e armazena no banco de dados

                    Notification::make()
                        ->title("Pedido de troca cancelado com sucesso")
                        ->body("Cancelou o pedido de troca com o professor {$ownername}, relativo à aula na sala {$currentRoom}, marcada para {$dayName} às {$timePeriod}.")
                        ->success()
                        ->sendToDatabase($requester); // Envia e armazena no banco de dados



                    return redirect($this->getResource()::getUrl('index'));
                });
        }



        $actions[] = DeleteAction::make('cancelarPedido')
            ->label('Eliminar Pedido')
            ->color('danger')
            ->visible(fn() => \Filament\Facades\Filament::auth()->user()?->teacher?->id === $this->record->id_teacher_requester)

            ->requiresConfirmation()
            ->modalHeading('Cancelar Pedido de Troca')
            ->modalSubheading('Isto irá eliminar o pedido e o horário criado para a troca.')
            ->after(function ($record) {
                $schedule = $record->scheduleNovo;

                if ($schedule) {
                    $schedule->delete(); // Apenas elimina o horário
                }
                $owner = $this->record->scheduleConflict?->teacher?->user;
                $idMarcacao =  $this->record->scheduleNovo?->id  ?? 'N/A';

                SchedulesResource::hoursCounterUpdate($schedule, true); // Atualiza o contador de horas

                Notification::make()
                    ->title('Pedido cancelado com sucesso')
                    ->body("O pedido de troca foi removido e o horário pendente: {$idMarcacao} foi eliminado.")
                    ->success()
                    ->sendToDatabase($owner);
            })
            ->successRedirectUrl($this->getResource()::getUrl('index'));


        $actions[] = Action::make('cancel')
            ->label('Cancelar')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');



        return $actions;
    }

    protected function getAvailableRooms(): array
    {

        $this->record->loadMissing('scheduleConflict.room');

        $conflict = $this->record->scheduleConflict;

        $edificioId = $conflict->room?->building_id;
        $idTimePeriod = $conflict->id_timeperiod;
        $idWeekday = $conflict->id_weekday;


        if (is_null($edificioId) || is_null($idTimePeriod) || is_null($idWeekday)) {
            return [];
        }

        //   dd($edificioId, $idTimePeriod, $idWeekday);

        return Room::where('building_id', $edificioId)
            ->whereDoesntHave('schedules', function ($query) use ($idTimePeriod, $idWeekday) {
                $query->where('id_timeperiod', $idTimePeriod)
                    ->where('id_weekday', $idWeekday);
            })
            ->get()
            ->unique('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
