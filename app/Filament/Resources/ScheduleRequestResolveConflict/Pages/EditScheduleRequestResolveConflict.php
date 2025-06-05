<?php

namespace App\Filament\Resources\ScheduleRequestResolveConflict\Pages;

use App\Filament\Resources\ScheduleRequestResolveConflict;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\Room;
use Filament\Facades\Filament; // Importar aqui
use Illuminate\Database\Eloquent\Builder;



class EditScheduleRequestResolveConflict extends EditRecord
{
    protected static string $resource = ScheduleRequestResolveConflict::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $this->record->loadMissing([
            'scheduleConflict.teacher',
            'scheduleConflict.room',
            'scheduleConflict.weekday',
            'scheduleConflict.timePeriod',
            'requester',
        ]);
        // dd($this->record->toArray());

        return $data;
    }

    protected function getFormActions(): array
{
    return [

        Action::make('aprovar')
            ->label('Aprovar Pedido')
            ->color('success')
            ->requiresConfirmation()
            ->form([
                Select::make('id_room_novo')
                    ->label('Sala Nova')
                    ->required()
                    ->preload()
                    ->options(fn () => $this->getAvailableRooms()),

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

                $this->record->scheduleNovo?->update([
                    'status' => 'Aprovado',
                ]);

                Notification::make()
                    ->title('Troca Aprovada')
                    ->success()
                    ->body('A troca foi aprovada com sucesso.')
                    ->send();
            }),

        Action::make('recusar')
            ->label('Recusar Pedido')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->update([
                    'status' => 'Recusado',
                ]);

                Notification::make()
                    ->title('Pedido recusado')
                    ->danger()
                    ->body('A troca foi recusada.')
                    ->send();
            }),
    ];
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



                
            