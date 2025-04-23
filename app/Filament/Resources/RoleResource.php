<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Dom\Text;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static bool $shouldRegisterNavigation = false;      // ESCONDER NO MENU

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Admin';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Role Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('guard_name')
                    ->label('Guard Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('web')
                    ->helperText('Informe o nome do guard'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->searchable(),
                TextColumn::make('name')->label('Role Name')->searchable(),

            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}







// namespace App\Filament\Resources;

// use App\Filament\Resources\RoleResource\Pages;
// use App\Filament\Resources\RoleResource\RelationManagers;
// use App\Models\Role;
// use Filament\Forms;
// use Filament\Forms\Form;
// use Filament\Resources\Resource;
// use Filament\Tables;
// use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\Textarea;
// use Filament\Tables\Columns\TextColumn;

// class RoleResource extends Resource
// {
//     protected static ?string $model = Role::class;

//     protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
//     protected static ?string $navigationGroup = 'Administração';
//     protected static ?string $navigationLabel = 'Perfis';

//     public static function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 TextInput::make('role')
//                     ->label('Nome do perfil')
//                     ->required()
//                     ->maxLength(255)
//                     ->placeholder('Nome do perfil')
//                     ->helperText('Informe o nome do perfil'),
//             ]);
//     }

//     public static function table(Table $table): Table
//     {
//         return $table
//             ->columns([
//                 TextColumn::make('id')
//                     ->label('ID')
//                     ->sortable()
//                     ->searchable(),
//                 TextColumn::make('role')
//                     ->label('Nome do perfil')
//                     ->sortable()
//                     ->searchable(),

//                 TextColumn::make('created_at')
//                     ->label('Criado em')
//                     ->dateTime()
//                     ->sortable()
//                     ->searchable(),
//                 TextColumn::make('updated_at')
//                     ->label('Atualizado em')
//                     ->dateTime()
//                     ->sortable()
//                     ->searchable(),
//             ])
//             ->filters([
//                 //
//             ])
//             ->actions([
//                 Tables\Actions\EditAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     Tables\Actions\DeleteBulkAction::make(),
//                 ]),
//             ]);
//     }

//     public static function getRelations(): array
//     {
//         return [
//             //
//         ];
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => Pages\ListRoles::route('/'),
//             'create' => Pages\CreateRole::route('/create'),
//             'edit' => Pages\EditRole::route('/{record}/edit'),
//         ];
//     }
// }
