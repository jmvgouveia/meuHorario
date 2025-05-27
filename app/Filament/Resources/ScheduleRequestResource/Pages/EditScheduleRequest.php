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



class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;

  protected function getFormActions(): array
{
    $status = $this->record->status;

    $actions = [];

    // Botão aceitar troca — só se NÃO estiver recusado
    if ($status !== 'Recusado') {
        $actions[] = Action::make('accept')
            ->label('Aceitar Troca')
            ->color('success')
            ->form([
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
                $this->record->scheduleConflict?->update(['status' => 'Trocado']);
                $this->record->scheduleNovo?->update(['status' => 'Aprovado']);

                Notification::make()
                    ->title('Troca Aprovada')
                    ->success()
                    ->body('A troca foi aprovada.')
                    ->send();
            });
    }

    // Botão recusar troca — só se NÃO estiver aprovado
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

    // Botão Escalar situação — só se estiver recusado
    if ($status === 'Recusado') {
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
                    logger('Escalando situação: ' . $data['response']),
                ]);
                Notification::make()
                    ->title('Situação Escalada')
                    ->warning()
                    ->body('O pedido foi escalado para análise.')
                    ->send();
            });
    }

    // Sempre adicionar o botão cancelar
    $actions[] = Action::make('cancel')
        ->label('Cancelar')
        ->url($this->getResource()::getUrl('index'))
        ->color('secondary');

    return $actions;
}

}
