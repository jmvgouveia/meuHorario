<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocenteResource\Pages;
use App\Filament\Resources\DocenteResource\RelationManagers;
use App\Models\Docente;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocenteResource extends Resource
{
    protected static ?string $model = Docente::class;
    protected static bool $shouldRegisterNavigation = false;      // ESCONDER NO MENU

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('numero_processo')
                    ->label('Numero de Processo')
                    ->required()
                    ->placeholder('123456'),
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->placeholder('nome completo'),
                    TextInput::make('sigla')
                    ->label('Sigla')
                    ->required()
                    ->placeholder('ABCD'),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->placeholder('email@email.com'),
                TextInput::make('address')
                    ->label('Morada')
                    ->placeholder('Avenia da Liberdade, nº 123'),
                Select::make('sexo')
                    ->label('Escolha uma opção:')
                    ->options([
                        'Masculino' => 'Masculino',
                        'Feminino' => 'Feminino',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_processo')
                    ->label('Numero de Processo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sigla')
                    ->label('Sigla')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Morada')
                    ->searchable()
                    ->sortable(),

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
            'index' => Pages\ListDocentes::route('/'),
            'create' => Pages\CreateDocente::route('/create'),
            'edit' => Pages\EditDocente::route('/{record}/edit'),
        ];
    }
}
