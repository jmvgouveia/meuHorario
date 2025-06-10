<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ClassesImporter;
use App\Filament\Resources\ClassesResource\Pages;
use App\Filament\Resources\ClassesResource\RelationManagers;
use App\Models\Classes;
use App\Models\Course;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class ClassesResource extends Resource
{
    protected static ?string $model = Classes::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Definições Horário';

    protected static ?string $navigationLabel = 'Turmas';
    protected static ?int $navigationSort = 4;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('class')
                    ->label('Nome da Turma')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza o nome da Turma'),

                Select::make('id_course')
                    ->label('Curso')
                    ->relationship('course', 'course')
                    ->placeholder('Escolha o curso')
                    ->required(),

                TextInput::make('year')
                    ->label('Ano')
                    ->numeric()
                    ->placeholder('Introduza o ano'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('class')
                    ->label('Nome da Turma')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.course')
                    ->label('Curso')
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Ano')
                    ->sortable()
                    ->searchable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])

            ->headerActions([

                Tables\Actions\ImportAction::make()
                    ->importer(ClassesImporter::class)
                    ->label('Importar Turmas')
                    ->icon('heroicon-o-arrow-up-tray')
                //  ->color('success')
                // ->action('importer')




                // Tables\Actions\ImportAction::make()
                //     ->label('Importar Turmas')
                //     ->icon('heroicon-o-arrow-up-tray')
                //     ->color('success')
                //     ->action('importer')
                //     ->importer(ClassesImporter::class),

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
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasses::route('/create'),
            'edit' => Pages\EditClasses::route('/{record}/edit'),
        ];
    }
}
