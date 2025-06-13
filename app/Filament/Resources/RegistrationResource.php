<?php

namespace App\Filament\Resources;

use App\Models\Classes;
use App\Models\Subject;
use App\Models\CourseSubjects;


use App\Filament\Resources\RegistrationResource\Pages;
use App\Filament\Resources\RegistrationResource\RelationManagers;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Imports\RegistrationImporter;
use Doctrine\DBAL\Schema\Schema;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\ImportAction;



class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;
    protected static ?string $navigationGroup = 'Gestão de Alunos';
    protected static ?string $navigationLabel = 'Matrículas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados Gerais')
                    ->description('Dados gerais da matrícula'),

                Select::make('id_student')
                    ->label('Aluno')
                    ->required()
                    ->searchable()
                    ->reactive() // necessário para reatividade com os outros campos
                    ->options(function () {
                        return \App\Models\Student::orderBy('id')
                            ->get()
                            ->mapWithKeys(fn($student) => [
                                $student->id => "{$student->studentnumber} - {$student->name}"
                            ]);
                    })
                    ->placeholder('Selecione o Aluno'),

                Select::make('id_course')
                    //->options(Nationality::all()->pluck('nationality','id'))
                    ->relationship('course', 'course')
                    ->label('Curso')
                    ->required()
                    // ->searchable()
                    ->placeholder('Selecione o Curso')
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('id_class', null)), // limpa a turma quando o curso muda

                Select::make('id_class')
                    ->label('Turma')
                    ->placeholder('Selecione a Turma')
                    ->helperText('Informe a turma do aluno')
                    ->options(function (callable $get) {
                        $courseId = $get('id_course');
                        if (!$courseId) return [];

                        return Classes::where('id_course', $courseId)->pluck('class', 'id');
                    })
                    ->reactive()
                    ->required() // ainda obrigatório, mas validaremos manualmente depois se não houver opções
                    ->disabled(function (callable $get) {
                        $courseId = $get('id_course');
                        if (!$courseId) return true;

                        return !Classes::where('id_course', $courseId)->exists();
                    })
                    ->hint(function (callable $get) {
                        $courseId = $get('id_course');
                        if ($courseId && !Classes::where('id_course', $courseId)->exists()) {
                            return 'Este curso não tem turmas disponíveis.';
                        }
                        return null;
                    }),



                Select::make('id_schoolyear')
                    ->default(fn() => DB::table('schoolyears')->where('active', true)->value('id'))
                    ->relationship('schoolyear', 'schoolyear')
                    ->label('Ano Lectivo')
                    ->required()
                    ->placeholder('Selecione o Ano Lectivo'),



                Section::make('📚 Disciplinas')
                    ->description('Selecione as disciplinas associadas ao curso e turma')
                    ->schema([
                        CheckboxList::make('subjects')
                            ->relationship('subjects', 'subject') // <- usa a relação no modelo Registration
                            //   ->label('Disciplinas')
                            ->label(false)
                            ->columns(5)
                            ->columnSpanFull()
                            ->reactive()
                            ->options(function (callable $get) {
                                $courseId = $get('id_course');
                                if (!$courseId) return [];

                                return \App\Models\Subject::whereIn('id', function ($query) use ($courseId) {
                                    $query->select('id_subject')
                                        ->from('course_subjects')
                                        ->where('id_course', $courseId);
                                })->pluck('subject', 'id');
                            }),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.studentnumber')
                    ->label('Nº Aluno')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('student.name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('course.course')
                    ->label('Curso')
                    //->limit(10)
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('class.class')
                    ->label('Turma')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Letivo')
                    ->sortable()
                    ->searchable(),


                TextColumn::make('subjects_list')
                    ->label('Disciplinas')
                    ->getStateUsing(function ($record) {
                        return $record->subjects->pluck('subject')->join(', ');
                    })
                    ->wrap()
                    ->sortable(false)
                    ->searchable(false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //  ImportAction::make()->importer(RegistrationImporter::class),
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
            //    RelationManagers\SubjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
