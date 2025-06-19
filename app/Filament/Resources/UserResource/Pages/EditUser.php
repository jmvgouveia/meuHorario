<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Support\Facades\Log;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $user = \Illuminate\Support\Facades\Auth::user();



        Notification::make()
            ->title('O teu perfil foi atualizado!')
            ->success()
            ->sendToDatabase($user);
        // Aqui podes adicionar lógica antes de salvar o modelo.
        // Por exemplo, validar ou modificar os dados do formulário.
    }
}
