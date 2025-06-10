<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Models\Teacher;
use App\Models\User;
use App\Models\TeacherHourCounter;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class CreateTeacher extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\TeacherResource::class;

    // Este método é chamado antes de salvar os dados no banco
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        //Log::debug('Dados recebidos para criação do professor:', $data);

        // Acessando os dados do 'user' dentro do array
        $userData = $data['user']; // Obtém o array de dados do usuário
        //   Log::debug('Dados para criação do professor:', $userData);
        // Criar o usuário (User)
        $user = User::create([
            'name' => $data['name'],               // Nome do professor
            'email' => $userData['email'],         // Acessando o email do 'user'
            'password' => Hash::make($userData['password']),  // Acessando a senha do 'user'
            // 'id_role' => 5, // ID do papel de professor
        ]);

        $horas = TeacherHourCounter::create([
            'id_teacher' => $user->id, // Associando o id do usuário ao professor
            'carga_horaria' => 22,
        ]); // Carga horária padrão
        // Inicializando as horas com 0
        // Acessando as horas do professor

        // Associar o id_user ao professor
        //     $data['id_user'] = $user->id;

        // Retorna os dados do professor com o id_user preenchido
        return $data;
    }

    // Você pode adicionar um redirecionamento para a página de listagem
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
