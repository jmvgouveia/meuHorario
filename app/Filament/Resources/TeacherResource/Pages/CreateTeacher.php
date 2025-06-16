<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Models\Teacher;
use App\Models\User;
use App\Models\TeacherHourCounter;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;



class CreateTeacher extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = $data['user'];

        // Validação
        $validator = Validator::make([
            'name' => $data['name'],
            'email' => $userData['email'],
            'password' => $userData['password'],
        ], [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                Notification::make()
                    ->title('Erro ao criar professor')
                    ->body($message)
                    ->danger()
                    ->persistent()
                    ->send();
            }

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // Criar o User
        $user = User::create([
            'name' => $data['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Associar id_user ao professor
        $data['id_user'] = $user->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        TeacherHourCounter::create([
            'id_teacher' => $record->id, // Agora já existe!
            'carga_horaria' => 26,
            'carga_componente_letiva' => 22,
            'carga_componente_naoletiva' => 4,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
