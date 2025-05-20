<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRolesResource\Pages;
use App\Filament\Resources\UserRolesResource\RelationManagers;
use App\Models\UserRoles;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;



class UserRolesResource extends Resource
{
    protected static ?string $model = UserRoles::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Perfis de Utilizadores';

    //Para evitar N+1 queries no carregamento
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->with('roles');
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('role')->distinct();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_user')
                    ->label('Usuário')
                    ->required()
                    ->searchable()
                    ->reactive() // necessário para reatividade com os outros campos
                    ->options(function () {
                        return User::orderBy('id')
                            ->get()
                            ->mapWithKeys(fn($user) => [
                                $user->id => "{$user->name}"
                            ]);
                    })
                    ->placeholder('Selecione o Usuário'),

                Select::make('id_role') // ← deve corresponder à função roles() no modelo
                    ->label('Role')
                    ->required()
                    ->searchable()
                    ->reactive() // necessário para reatividade com os outros campos
                    ->options(function () {
                        return Role::orderBy('id')
                            ->get()
                            ->mapWithKeys(fn($role) => [
                                $role->id => "{$role->role}"
                            ]);
                    })
                    ->placeholder('Selecione o Usuário'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role.role')
                    ->label('Papéis')
                    ->badge()
                    ->colors([
                        'danger' => 'Admin',
                        'success' => 'Professor',
                        'info' => 'Gestor',
                        'warning' => 'Convidado',
                    ]),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRoles::route('/'),
            'create' => Pages\CreateUserRoles::route('/create'),
            'edit' => Pages\EditUserRoles::route('/{record}/edit'),
        ];
    }
}
