<?php

namespace App\Filament\Resources\ScheduleRequestResolveConflict\Pages;

use App\Filament\Resources\ScheduleRequestResolveConflict;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;


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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aprovar')
                ->label('Aprovar Pedido')
                ->color('success')
                ->visible(fn($record) => $record->status === 'Pendente')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'Aprovado',
                    ]);
                    $this->notify('success', 'Pedido aprovado com sucesso!');
                }),

            Action::make('recusar')
                ->label('Recusar Pedido')
                ->color('danger')
                ->visible(fn($record) => $record->status === 'Pendente')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'Recusado',
                    ]);
                    $this->notify('success', 'Pedido recusado.');
                }),
        ];
    }
}
