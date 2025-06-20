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



class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;


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
                            ->required()
                            ->preload()
                            ->options(fn() => $this->getAvailableRooms()),

                        Textarea::make('response')
                            ->label('Justificação para Aprovação')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data) {
                        // dados para a notificação

                        $schedule = $this->record->scheduleConflict;
                        $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                        $salaNova = \App\Models\Room::find($data['id_room_novo'])?->name ?? 'desconhecida';

                        $requester = $this->record->requester?->user;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $owner = $this->record->scheduleConflict?->teacher?->user;
                        $ownername = $owner?->name ?? 'desconhecido';



                        $this->record->update([
                            'status' => 'Aprovado',
                            'response' => $data['response'],
                        ]);

                        $this->record->scheduleConflict?->update([
                            'status' => 'Aprovado',
                            'id_room' => $data['id_room_novo'],
                        ]);

                        $this->record->scheduleNovo?->update(['status' => 'Aprovado']);

                        Notification::make()
                            ->title('Troca Aprovada')
                            ->success()
                            ->body("Aprovou o pedido de {$requestername} na troca da sala: {$salaAntiga} para a sala: {$salaNova}.")
                            ->sendToDatabase($owner); // Envia e armazena no banco de dados

                        Notification::make()
                            ->title('Troca Aprovada')
                            ->success()
                            ->body("Aprovou o pedido de {$requestername} na troca da sala: {$salaAntiga} para a sala: {$salaNova}.")
                            ->send();

                        Notification::make()
                            ->title('Troca Aprovada')
                            ->success()
                            ->body("O seu pedido de troca para a sala: {$salaAntiga} foi aceite por {$ownername}.")
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
                    ->action(function (array $data) {
                        $this->record->update([
                            'status' => 'Recusado',
                            'response' => $data['response'],
                            'response_at' => now(),
                        ]);


                        $schedule = $this->record->scheduleConflict;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                        $requester = $this->record->requester?->user;
                        $ownername = $owner?->name ?? 'desconhecido';

                        Notification::make()
                            ->title('Troca Recusada')
                            ->danger()
                            ->body("Recusou a troca de sala com {$requestername} na sala: {$salaAntiga}.")
                            ->send();

                        Notification::make()
                            ->title('Troca Recusada')
                            ->danger()
                            ->body("O seu pedido de troca da sala {$salaAntiga} foi recusado por {$ownername}.")
                            ->sendToDatabase($requester); // Envia e armaz


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
                    $this->record->update([
                        'status' => 'Escalado',
                        'justification_escalada' => $data['justification_escalada'],
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


                    Notification::make()
                        ->title('Pedido Escalado')
                        ->warning()
                        ->body('O seu pedido de troca foi escalado para análise pela Direção Pedagógica.');

                    // Notifica o dono do horário original
                    Notification::make()
                        ->title('Pedido Escalado')
                        ->warning()
                        ->body("O pedido de troca do professor {$requestername} foi escalado para análise pela Direção Pedagógica.")
                        ->sendToDatabase($owner); // Envia e armazena no banco de dados

                    Notification::make()
                        ->title('Pedido Escalado')
                        ->warning()
                        ->body("O seu pedido de troca ID:{$idMarcacao} com professor {$ownername} foi escalado para análise pela Direção Pedagógica.")
                        ->sendToDatabase($requester);



                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // ✅ Quem fez o pedido pode cancelar (se ainda estiver pendente)
        if (($isRequestOwner || $isGestor)&& $status === 'Pendente') {
            $actions[] = Action::make('cancelRequest')
                ->label('Cancelar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar este pedido de troca?')
                ->action(function () {
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
                        ->title('Pedido Cancelado')
                        ->success()
                        ->body("O pedido de troca do professor {$requestername} foi cancelado.")
                        ->sendToDatabase($owner); // Envia e armazena no banco de dados

                    Notification::make()
                        ->title('Pedido Cancelado')
                        ->success()
                        ->body("Cancelou o seu pedido de troca com o professor {$ownername}.")

                        ->sendToDatabase($requester); // Envia e armazena no banco de dados



                    return redirect($this->getResource()::getUrl('index'));
                });
        }







        $actions[] = Action::make('cancel')
            ->label('Cancelar')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');




        // Botão sempre presente para voltar
        // $actions[] = Action::make('marcarComoEliminado')
        //     ->label('Marcar como Eliminado')
        //     ->color('danger')
        //     ->requiresConfirmation()
        //     ->action(function () {
        //         $this->record->update(['status' => 'Eliminado']);

        //         // Elimina o pedido de troca
        //         $this->record->scheduleNovo?->update(['status' => 'Eliminado']);

        //         Notification::make()
        //             ->title("Pedido de troca marcado como eliminado")
        //             ->success()
        //             ->body("O pedido de troca foi marcado como eliminado.")
        //             ->sendToDatabase(Filament::auth()->user());

        //         return redirect($this->getResource()::getUrl('index'));
        //     });



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
