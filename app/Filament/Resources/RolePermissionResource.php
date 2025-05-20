<?php

namespace App\Filament\Resources;

use App\Models\Role;
use App\Models\Permission;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Resources\Table;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Facades\Log; // Certifique-se de importar o Log
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;


class RolePermissionResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static bool $shouldRegisterNavigation = false;      // ESCONDER NO MENU

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Perfis-Permissões';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('role')
                    ->label('Role Name')
                    ->required(),

                CheckboxList::make('permissions')
                    ->options(
                        Permission::all()->pluck('permission', 'id')->map(function ($name, $id) {
                            // Se o nome da permissão for null, fornecemos um valor padrão (fallback)
                            return $name ?? "Unknown Permission #$id"; // Fallback com o id da permissão
                        })
                    )
                    ->label('Permissões')
                    ->required()
                    ->default(function ($state) {
                        // Usar `optional` para garantir que o $state não seja nulo
                        if (optional($state)->exists) {
                            // Retornar os IDs das permissões associadas ao Role
                            return $state->permissions->pluck('id')->toArray();
                        }

                        // Caso contrário, retornar um array vazio
                        return [];
                    }),
            ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Role::with('permissions'))  // Carrega a relação permissions
            ->columns([
                TextColumn::make('role')
                    ->label('Role Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->formatStateUsing(function ($state) {
                        // Adiciona um log para verificar o que está sendo passado como $state

                        //Log::info('State type: ' . gettype($state));  // Registra o tipo de $state
                        // Log::info('State content: ' . print_r($state, true));  // Registra o conteúdo de $state

                        // Se o estado for uma string (JSON), tentamos deserializar
                        if (is_string($state)) {
                            // Converte a string JSON para um array
                            $state = json_decode("[$state]", true);  // A inclusão de colchetes [] garante que seja um array de objetos
                        }

                        // Verifica se agora é um array ou coleção
                        if (is_array($state)) {
                            // A coleção de permissões está sendo passada
                            return collect($state)->pluck('permission')->implode(', ');  // Pluck os nomes das permissões
                        }

                        return 'No permissions';
                    }),
            ]);
    }



    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\RolePermissionResource\Pages\ListRolePermissions::route('/'),
            'create' => \App\Filament\Resources\RolePermissionResource\Pages\CreateRolePermission::route('/create'),
            'edit' => \App\Filament\Resources\RolePermissionResource\Pages\EditRolePermission::route('/{record}/edit'),
        ];
    }
}
