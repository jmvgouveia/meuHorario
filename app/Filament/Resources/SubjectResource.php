<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Imports\SubjectImporter;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Definições Horário';

    protected static ?string $navigationLabel = 'Disciplinas';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados Gerais')
                    ->description('Dados gerais da disciplina'),
                TextInput::make('subject')
                    ->label('Disciplina')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Disciplina')
                    ->helperText('Informe a disciplina'),
                TextInput::make('acronym')
                    ->label('Abreviação')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Descrição')
                    ->helperText('Informe a descrição'),

                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'Letiva' => 'Letiva',
                        'Não Letiva' => 'Não Letiva',
                    ])
                    ->default('Letiva')
                    ->helperText('Selecione o tipo de disciplina'),
                // Section::make('Professor(s)')->schema([
                //     Select::make('teachers')
                //         ->label('Professor(es)')
                //         ->multiple()
                //         ->relationship('teachers', 'name')
                //         ->preload()
                //         ->searchable()
                //         ->placeholder('Selecione o(s) professor(es)')
                //         ->helperText('Selecione o(s) professor(es) que leccionam a disciplina')
                // ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('Disciplina')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('acronym')
                    ->label('Sigla')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('teachers_count')
                //     ->label('Número de Professores')
                //     ->counts('teachers')  // Conta o número de professores relacionados
                //     ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(SubjectImporter::class)
                    ->label('Importar Disciplinas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
                // Tables\Actions\CreateAction::make()
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
            // SubjectResource\RelationManagers\TeachersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
