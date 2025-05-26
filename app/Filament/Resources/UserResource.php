<?php

namespace App\Filament\Resources;

use Spatie\Permission\Models\Role;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Utilizadores';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Preencha com o nome completo'),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Preencha com o endereco de email'),
                TextInput::make('password')
                    ->label('Senha')
                    ->required()
                    ->minLength(5)
                    ->placeholder('Introduza a senha')
                    ->password()
                    ->dehydrated(fn($state) => filled($state)),
                Select::make('roles')
                    ->label('Papel')
                    ->multiple()
                    ->relationship('roles', 'role')
                    ->options(Role::all()->pluck('role', 'id'))
                    ->preload(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.role')
                    ->label('Perfis')
                    ->badge()
                    //->list()
                    ->colors([
                        'danger' => 'Admin',
                        'success' => 'Professor',
                        'info' => 'Gestor',
                        'warning' => 'Convidado',
                    ]),

                // TextColumn::make('roles.role')
                //     ->label('Papel')
                //     ->badge()
                //     ->colors([
                //         'danger' => 'Admin',
                //         'success' => 'Professor',
                //         'info' => 'Gestor',
                //         'warning' => 'convidado',
                //     ]),
                // TextColumn::make('created_at')
                //     ->label('Criado em')
                //     ->dateTime()
                //     ->sortable(),
                // TextColumn::make('updated_at')
                //     ->label('Atualizado em')
                //     ->dateTime()
                //     ->sortable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
