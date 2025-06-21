<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\Teacher;
use Dom\Text;
use Faker\Core\File;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use App\Filament\Imports\TeacherImporter;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Professores';
    protected static ?string $navigationLabel = 'Professores';

    protected static ?int $navigationSort = 7;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([

                        TextInput::make('teachernumber')
                            ->label('Numero de Professor')
                            ->required()
                            ->numeric()
                            ->placeholder('Exempo: 123456'),

                        TextInput::make('acronym')
                            ->label('Sigla')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Introduza a sigla'),

                        DatePicker::make('birthdate')
                            ->label('Data de Nascimento')
                            ->required()
                            ->placeholder('Selecione a data de nascimento'),

                        DatePicker::make('startingdate')
                            ->label('Data de inicio de função')
                            ->required()
                            ->placeholder('Selecione a data de de inicio de função'),

                    ])->columns(4),


                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza o nome completo')
                    ->columnSpanFull(),


                TextInput::make('user.email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->columnSpanFull(),

                TextInput::make('user.password')
                    ->label('Senha')
                    ->password()
                    ->minLength(5)
                    ->nullable()
                    ->placeholder('Deixe em branco para manter a atual')
                    ->columnSpanFull(),

                group::make()

                    ->schema([



                        Select::make('id_gender')
                            //->options(Gender::all()->pluck('gender','id'))
                            ->relationship('genders', 'gender')
                            ->label('Gênero')
                            ->required()
                            ->placeholder('Selecione o gênero'),

                        Select::make('id_nationality')
                            //->options(Nationality::all()->pluck('nationality','id'))
                            ->relationship('nationalities', 'nationality')
                            ->label('Nacionalidade')
                            ->required()
                            ->placeholder('Selecione o Nacionalidade'),

                        Select::make('id_qualifications')
                            //->options(Nationality::all()->pluck('nationality','id'))
                            ->relationship('qualifications', 'qualification')
                            ->label('Habilitações')
                            ->required()
                            ->placeholder('Selecione a Habilitação'),

                        Select::make('id_department')
                            ->relationship('departments', 'department')
                            ->label('Departamento')
                            ->required()
                            ->placeholder('Selecione a departamento'),

                        Select::make('id_professionalrelationship')
                            ->relationship('professionalrelationships', 'professional_relationship')
                            ->label('Relação Profissional')
                            ->required()
                            ->placeholder('Selecione a Relação Profissional'),

                        Select::make('id_contractualrelationship')
                            ->relationship('contractualrelationship', 'contractual_relationship')
                            ->label('Relação Contratual')
                            ->required()
                            ->placeholder('Selecione a Relação Contratual'),

                        Select::make('id_salaryscale')
                            ->relationship('salaryscales', 'scale')
                            ->label('Escalão Salarial')
                            ->required()
                            ->placeholder('Selecione a Escalão Salarial'),

                    ])->columns(2),


                // Section::make('Disciplinas do Professor')->schema([
                //     Select::make('subject')
                //         ->label('Disciplina(s)')
                //         ->multiple()
                //         ->relationship('subject', 'subject')
                //         ->preload()
                //         ->searchable()
                //         ->placeholder('Selecione a(s) disciplina(s) que o professor lecciona')
                // ]),




                //
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teachernumber')
                    ->label('N. Professor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('acronym')
                    ->label('Sigla')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('departments.department')
                    ->label('Departamento')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TeacherImporter::class)
                    ->label('Import Teacher(s)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
                // Tables\Actions\CreateAction::make(),
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
            //  GenderResource\RelationManagers\TeachersRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
