<?php

namespace App\Filament\Resources;

use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationGroup = 'Gestão de Alunos';
    protected static ?string $navigationLabel = 'Alunos';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados Gerais')
                    ->description('Dados gerais do aluno'),
                Grid::make(2)->schema([
                    TextInput::make('studentnumber')
                        ->label('Número de Aluno')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Número de Aluno')
                        ->helperText('Informe o número de aluno'),
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Nome')
                        ->helperText('Informe o nome do aluno'),

                    DatePicker::make('birthdate')
                        ->label('Data de Nascimento')
                        ->required(),


                    Select::make('id_gender')
                        //->options(Gender::all()->pluck('gender','id'))
                        ->relationship('genders', 'gender')
                        ->label('Gênero')
                        ->required()
                        ->placeholder('Selecione o gênero'),

                ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('studentnumber')
                    ->label('Número de Aluno')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Número de Aluno'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Nome'),
                //
            ])

            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(StudentImporter::class)
                    ->label('Import Students')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
