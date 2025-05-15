<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseSubjectsResource\Pages;
use App\Filament\Resources\CourseSubjectsResource\RelationManagers;
use App\Models\CourseSubjects;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Imports\CourseSubjectsImporter;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;


class CourseSubjectsResource extends Resource
{
    protected static ?string $model = CourseSubjects::class;
    protected static ?string $navigationGroup = 'Gestão de Cursos';
    protected static ?string $navigationLabel = 'Cursos e Disciplinas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Curso e Disciplina')
                    ->description('Associação de disciplina(s) a um curso e ano lectivo'),

                Select::make('id_course')
                    ->label('Curso')
                    ->required()
                    ->placeholder('Selecione o Curso')
                    ->relationship('course', 'course')
                    ->reactive()
                    ->helperText('Selecione o curso'),

                Select::make('id_subject')
                    ->label('Disciplina')
                    ->required()
                    ->placeholder('Selecione a disciplina')
                    ->relationship('subject', 'subject')
                    ->reactive(),

                Select::make('id_schoolyear')
                    ->label('Ano Lectivo')
                    ->required()
                    ->relationship('schoolyear', 'schoolyear')
                    ->placeholder('Ano Lectivo')
                    ->helperText('Informe o ano lectivo'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.course')
                    ->label('Curso')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Curso'),

                TextColumn::make('subject.subject')
                    ->label('Disciplina')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Disciplina'),

                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Lectivo')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Ano Lectivo'),

            ])->filters([
                //
            ])->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(CourseSubjectsImporter::class),

            ])->actions([
                //
            ])->bulkActions([
                //
            ])->emptyStateActions([
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
            'index' => Pages\ListCourseSubjects::route('/'),
            'create' => Pages\CreateCourseSubjects::route('/create'),
            'edit' => Pages\EditCourseSubjects::route('/{record}/edit'),
        ];
    }
}
