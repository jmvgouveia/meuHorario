<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchedulesResource\Pages;
use App\Filament\Resources\SchedulesResource\RelationManagers;
use App\Models\Building;
use App\Models\Classes;
use App\Models\Room;
use App\Models\Schedules;
use App\Models\Subject;
use App\Models\TimePeriod;
use App\Models\WeekDays;
use App\Models\SchoolYears;
use App\Models\Teacher;
use App\Models\Registration;
use App\Models\Student;

use Dom\Text;
use Faker\Core\Color;
use Filament\Facades\Filament;
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
use Filament\Forms\Components\Badge;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\ColorPicker;
use Illuminate\Console\Scheduling\Schedule;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions as ActionGroup;

use Filament\Forms\Components\Actions\Action;

use Filament\Tables\Actions\DeleteAction;






class SchedulesResource extends Resource
{



    protected static ?string $model = Schedules::class;

    protected static ?string $navigationGroup = 'Horários';
    protected static ?string $navigationLabel = 'Marcação de Horários';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public ?Schedule $conflictingSchedule = null;

    // public static function canViewAny(): bool
    // {
    //     return Auth::user() && Auth::user()->hasPermissionTo('ver_horarios');
    // }

    // public static function canCreate(): bool
    // {
    //     return auth()->user()?->can('editar_horarios');
    // }

    // public static function canEdit(Model $record): bool
    // {
    //     return auth()->user()?->can('editar_horarios');
    // }




    // Filtrar os horários para mostrar apenas os do professor autenticado
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Verifica se o utilizador está autenticado e é um professor com registo
        if (Auth::check() && Auth::user()->teacher) {
            $teacherId = Auth::user()->teacher->id;

            // Filtra os horários para mostrar apenas os do professor autenticado
            $query->where('id_teacher', $teacherId);
        }

        return $query;
    }

    public function atualizarEstado($get, $set)
    {
        $sala = $get('id_room');
        $inicio = $get('inicio');

        if (!$sala || !$inicio) {
            $set('estado', null);
            return;
        }

        // Simula uma verificação de conflitos (aqui seria um query real à BD)
        $conflitos = Schedule::where('room_id', $sala)
            ->where('start', $inicio)
            ->exists();

        $estado = $conflitos ? 'ocupado' : 'disponível'; // ou 'em conflito', se houver lógica extra
        $set('estado', $estado);
    }



    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Section::make('Local da Aula')
                    ->description('Selecione o núcleo/pólo e a sala onde será dada a aula')
                    ->schema([

                        Grid::make(2)
                            ->schema([
                                Select::make('building_id')
                                    ->label('Núcleo ou Pólo')
                                    ->required()
                                    ->options(Building::all()->pluck('name', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('id_room', null))
                                    ->placeholder('Selecione o local da aula'),

                                Select::make('id_room')
                                    ->label('Sala')
                                    ->required()
                                    ->options(function (callable $get) {
                                        $buildingId = $get('building_id');
                                        if (!$buildingId) return [];
                                        return Room::where('building_id', $buildingId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Selecione a sala')
                                    ->reactive(),
                            ]),
                    ]),

                Section::make('Dia / Hora')
                    ->description('Informe quando a aula será realizada')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_weekday')
                                    ->label('Dia da Semana')
                                    ->required()
                                    ->options(WeekDays::all()->pluck('weekday', 'id'))
                                    ->placeholder('Selecione o dia da semana'),

                                Select::make('id_timeperiod')
                                    ->label('Hora de Início')
                                    ->required()
                                    ->placeholder('Selecione a hora de início')
                                    ->options(TimePeriod::all()->pluck('description', 'id'))
                                    ->reactive(),



                            ]),
                    ]),

                Section::make('Composição da Aula')
                    ->description('Defina a disciplina, turmas e alunos envolvidos')
                    ->schema([
                        Select::make('id_subject')
                            ->label('Disciplina')
                            ->required()
                            ->reactive()
                            ->options(function () {
                                $userId = \Illuminate\Support\Facades\Auth::id();
                                $teacher = Teacher::where('id_user', $userId)->first();
                                if (!$teacher) return collect(['' => 'Este utilizador não é um professor']);
                                $activeYear = SchoolYears::where('active', true)->first();
                                if (!$activeYear) return collect(['' => 'Nenhum ano letivo ativo']);
                                $subjects = Subject::whereHas('teachers', function ($query) use ($teacher, $activeYear) {
                                    $query->where('id_teacher', $teacher->id)
                                        ->where('teacher_subjects.id_schoolyear', $activeYear->id);
                                })->pluck('subject', 'id');
                                return $subjects->isEmpty()
                                    ? collect(['' => 'Nenhuma disciplina atribuída neste ano letivo'])
                                    : $subjects;
                            })
                            ->placeholder('Escolha a disciplina')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('id_subject', $state);
                                $set('id_classes', []);
                                $set('alunos', []);
                            }),

                        Select::make('id_classes')
                            ->label('Turmas')
                            ->multiple()
                            ->helperText('Selecione a(s) turma(s) que vão assistir à aula')
                            ->reactive()
                            // ->default(fn(?Schedule $record) => $record?->classes()->pluck('id')->toArray())
                            ->afterStateHydrated(function (callable $set, ?Schedules $record) {
                                $set('id_classes', $record?->classes()->pluck('classes.id')->toArray());
                            })
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                if (!$subjectId) return [];
                                $subject = Subject::find($subjectId);
                                if (!$subject) return [];
                                $courseIds = $subject->courses()->select('courses.id')->pluck('id');
                                return Classes::whereIn('id_course', $courseIds)->pluck('class', 'id');
                            })
                            ->afterStateUpdated(fn($state, callable $set) => $set('alunos', [])),

                        CheckboxList::make('students')
                            ->label('Alunos matriculados na disciplina')
                            ->helperText('Selecione os alunos que vão assistir à aula')
                            //->default(fn(?Schedule $record) => $record?->students()->pluck('id')->toArray())
                            // ->afterStateHydrated(function (callable $set, ?Schedules $record) {
                            //     if ($record) {
                            //         $ids = $record->students()->pluck('id_student')->toArray(); // ← chave correta da pivot
                            //         $set('students', $ids);
                            //     }
                            // })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!empty($state)) {
                                    $numeros = \App\Models\Student::whereIn('id', $state)
                                        ->pluck('studentnumber')
                                        ->sort()
                                        ->implode(', ');

                                    $set('turno', $numeros);
                                } else {
                                    $set('turno', null); // Limpa se não houver alunos
                                }
                            })
                            ->columns(4)
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $classIds = $get('id_classes') ?? [];
                                $schoolYear = \App\Models\SchoolYears::where('active', true)->first();

                                if (!$subjectId || !$schoolYear) return [];

                                $registrationIds = DB::table('registrations_subjects')
                                    ->where('id_subject', $subjectId)
                                    ->pluck('id_registration');

                                if ($registrationIds->isEmpty()) return [];

                                $query = \App\Models\Registration::with('student')
                                    ->whereIn('id', $registrationIds)
                                    ->where('id_schoolyear', $schoolYear->id);

                                if (!empty($classIds)) {
                                    $query->whereIn('id_class', $classIds);
                                }

                                return $query->get()->mapWithKeys(function ($registration) {
                                    $student = $registration->student;
                                    if (!$student) return [];

                                    return [
                                        $registration->id_student => "{$student->studentnumber} - {$student->name}",
                                    ];
                                });
                            }),
                    ]),

                Section::make('Turno (opcional)')
                    ->description('Selecione o turno da turma ')
                    ->schema([


                        Select::make('turno')
                            ->label('Turno')
                            ->reactive()
                            ->required(fn(callable $get) => !empty($get('students')))
                            ->default('NA')
                            ->options(function (callable $get) {
                                $alunoIds = $get('students') ?? [];


                                if (count($alunoIds) > 0) {
                                    $alunos = \App\Models\Student::whereIn('id', $alunoIds)
                                        ->get()
                                        ->sortBy('studentnumber')
                                        ->map(fn($aluno) => "{$aluno->studentnumber} - {$aluno->name}")
                                        ->implode(' ; ');

                                    return [$alunos => "Turno: $alunos"];
                                }



                                return [
                                    'turmaA' => 'Turma A',
                                    'turmaB' => 'Turma B',
                                    'turmaC' => 'Turma C',
                                    'turmaD' => 'Turma D',
                                ];
                            })
                            ->placeholder('Em caso de ser a turma toda deixar em branco'),



                    ]),

                ActionGroup::make([
                    Action::make('justificarConflito')
                        ->label('Solicitar Troca de Horário')
                        ->visible(fn($livewire) => $livewire->conflictingSchedule !== null)
                        ->modalHeading('Justificação do Conflito')
                        ->modalSubmitActionLabel('Submeter Justificação')
                        ->modalCancelActionLabel('Cancelar')
                        ->form([

                            Textarea::make('justification')
                                ->label('Escreva a justificação')
                                ->required()
                                ->minLength(10),
                        ])
                        ->action(fn(array $data, $livewire) => $livewire->submitJustification($data)),
                ]),
            ]);
    }





    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->sortable()
                //     ->toggleable()
                //     ->searchable(),
                // TextColumn::make('teacher.teachernumber')
                //     ->label('Professor')
                //     ->sortable()
                //     ->toggleable()
                //     ->searchable(),
                // TextColumn::make('teacher.name')
                //     ->label('Nome do Professor')
                //     ->sortable()
                //     ->toggleable()
                //     ->searchable(),
                TextColumn::make('subject.subject')
                    ->label('Disciplina')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('timeperiod.description')
                    ->label('Hora da Aula')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('room.building.name')
                    ->label('Pólo')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('room.name')
                    ->label('Sala')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        Log::info('*** AFTER DELETE acionado para eliminacao de aula', ['id' => $record->id]);

                        $record->load('subject');
                        Log::info('Subject associado:', ['subject' => $record->subject]);

                        $tipoDisciplina = strtolower(trim($record->subject->type ?? ''));
                        Log::info('Tipo recebido no record', ['tipo' => $tipoDisciplina]);

                        Log::info('Tipo recebido no record', ['tipo' => $tipoDisciplina]);

                        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                        if (!$counter) {
                            Log::warning('Counter não encontrado para professor.', ['id_teacher' => $record->id_teacher]);
                            return;
                        }

                        if ($tipoDisciplina === 'nao letiva') {
                            $counter->carga_componente_naoletiva = max(2, $counter->carga_componente_naoletiva + 1);
                            $componente = 'Não Letiva';
                        } else {
                            $counter->carga_componente_letiva = max(0, $counter->carga_componente_letiva + 1);
                            $componente = 'Letiva';
                        }

                        $counter->carga_horaria = $counter->carga_componente_letiva + $counter->carga_componente_naoletiva;
                        $counter->save();

                        Log::info('Carga horária reposta após exclusão.', [
                            'id_teacher' => $record->id_teacher,
                            'componente' => $componente,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($records) {
                        foreach ($records as $record) {
                            //$record->load('timeReduction');

                            $reduction = $record->position;
                            $counter = \App\Models\TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                            if ($reduction && $counter) {
                                $valorLetiva = floatval($reduction->position_reduction_value ?? 0);
                                $valorNaoLetiva = floatval($reduction->position_reduction_value_nl ?? 0);

                                $novaLetiva = $counter->carga_componente_letiva + $valorLetiva;
                                $novaNaoLetiva = $counter->carga_componente_naoletiva + $valorNaoLetiva;

                                $counter->carga_componente_letiva = $novaLetiva;
                                $counter->carga_componente_naoletiva = $novaNaoLetiva;
                                $counter->carga_horaria = $novaLetiva + $novaNaoLetiva;
                                $counter->save();
                            }
                        }
                    }),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedules::route('/create'),
            'edit' => Pages\EditSchedules::route('/{record}/edit'),
        ];
    }
}
