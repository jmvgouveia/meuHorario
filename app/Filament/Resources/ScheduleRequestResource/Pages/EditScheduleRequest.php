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
use Filament\Facades\Filament;  // <-- Importar aqui




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

        $status = $this->record->status;

        $actions = [];

        // ✅ Quem pode aprovar ou recusar (dono do horário original)
        if ($isReceiver) {

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
                            ->label('Justificação')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data) {
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
                            ->body('A troca foi aprovada.')
                            ->send();
                    });
            }

            if ($status !== 'Aprovado') {
                $actions[] = Action::make('reject')
                    ->label('Recusar Troca')
                    ->color('danger')
                    ->form([
                        Textarea::make('response')
                            ->label('Justificação')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data) {
                        $this->record->update([
                            'status' => 'Recusado',
                            'response' => $data['response'],
                        ]);
                        Notification::make()
                            ->title('Troca Recusada')
                            ->danger()
                            ->body('A troca foi recusada.')
                            ->send();
                    });
            }
        }

        //
        if ($isRequestOwner && $status === 'Recusado') {
            $actions[] = Action::make('escalar')
                ->label('Escalar Situação')
                ->color('warning')
                ->form([
                    Textarea::make('response')
                        ->label('Justificação para Escalar')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'Escalado',
                        'justification_escalada' => $data['response'],
                    ]);
                    Notification::make()
                        ->title('Situação Escalada')
                        ->warning()
                        ->body('O pedido foi escalado para análise.')
                        ->send();
                });
        }

        // ✅ Quem fez o pedido pode cancelar (se ainda estiver pendente)
        if ($isRequestOwner && $status === 'Pendente') {
            $actions[] = Action::make('cancelRequest')
                ->label('Cancelar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar este pedido de troca?')
                ->action(function () {
                    $this->record->update(['status' => 'Cancelado']);
                    $this->record->scheduleNovo?->update(['status' => 'Cancelado']);

                    Notification::make()
                        ->title('Pedido Cancelado')
                        ->success()
                        ->body('O seu pedido de troca foi cancelado com sucesso.')
                        ->send();

                    return redirect($this->getResource()::getUrl('index'));
                });
        }



        // Botão sempre presente para voltar
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

        if (!$edificioId || !$idTimePeriod || !$idWeekday) {
            return [];
        }

        return Room::where('building_id', $edificioId)
            ->whereDoesntHave('schedules', function ($query) use ($idTimePeriod, $idWeekday) {
                $query->where('id_timeperiod', $idTimePeriod)
                    ->where('id_weekday', $idWeekday);
            })
            ->pluck('name', 'id')
            ->toArray();
    }
}
