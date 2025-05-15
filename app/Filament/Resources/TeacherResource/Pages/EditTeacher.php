<?php

// namespace App\Filament\Resources\TeacherResource\Pages;

// use App\Filament\Resources\TeacherResource;
// use App\Models\User;
// use Filament\Actions;
// use Filament\Resources\Pages\EditRecord;
// use Illuminate\Support\Facades\Hash;

// class EditTeacher extends EditRecord
// {
//     protected static string $resource = TeacherResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\DeleteAction::make(),
//         ];
//     }

//     protected function mutateFormDataBeforeFill(array $data): array
//     {
//         $teacher = $this->record->load('user');

//         if ($teacher->user) {
//             $data['user.email'] = $teacher->user->email;
//             // password não se preenche por segurança
//         }

//         return $data;
//     }

//     protected function mutateFormDataBeforeSave(array $data): array
//     {
//         $teacher = $this->record->load('user');

//         info('DADOS DO FORM', $data);
//         info('UTILIZADOR DO PROFESSOR', ['user' => $teacher->user]);

//         if ($teacher->user) {
//             if (!empty($data['user.email'])) {
//                 $teacher->user->email = $data['user.email'];
//             }

//             if (!empty($data['user.password'])) {
//                 $teacher->user->password = Hash::make($data['user.password']);
//             }

//             //            $teacher->user->save();
//             $teacher->user->saveOrFail();
//             dd($teacher->user->fresh());
//         }

//         unset($data['user']);

//         return $data;
//     }
// }
namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $teacher = $this->record->load('user');

        $data['user']['email'] = $teacher->user->email ?? '';
        // ⚠️ Não preencher password por segurança
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Atualizar campos do utilizador, se existir
        if ($record->user) {
            // Atualizar o nome do utilizador com o nome do professor
            $record->user->name = $data['name']; // Atualiza o nome do utilizador com o nome do professor

            // Atualizar o email do utilizador
            $record->user->email = $data['user']['email'];

            // Atualizar a senha, se fornecida
            if (!empty($data['user']['password'])) {
                $record->user->password = Hash::make($data['user']['password']);
            }

            // Salvar as mudanças no utilizador
            $record->user->saveOrFail();
        }

        // Remover a parte do user dos dados do Teacher
        unset($data['user']);

        // Atualizar o professor normalmente
        $record->updateOrFail($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
