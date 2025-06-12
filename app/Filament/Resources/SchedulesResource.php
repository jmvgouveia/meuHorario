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
use Symfony\Component\HttpFoundation\StreamedResponse;

use Dom\Text;
use Faker\Core\Color;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
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



use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\BulkAction as TablesBulkAction;





class SchedulesResource extends Resource
{

    protected static ?string $model = Schedules::class;
    protected static ?string $navigationGroup = 'Horários';
    protected static ?string $navigationLabel = 'Marcação de Horários';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public ?Schedule $conflictingSchedule = null;


    public static function exportSchedules(?Collection $records = null): StreamedResponse
    {
        $schedules = $records ?? \App\Models\Schedules::with(['teacher', 'room', 'subject', 'weekday', 'timePeriod', 'classes', 'students'])->get();

        $now = now()->format('Y-m-d_H-i');
        $filename = "horarios-{$now}.txt";

        return response()->streamDownload(function () use ($schedules) {
            $handle = fopen('php://output', 'w');

            foreach ($schedules as $schedule) {
                $turmaAlunos = [];

                // Verifica se há alunos selecionados
                if ($schedule->students->isNotEmpty()) {
                    foreach ($schedule->students as $student) {
                        $registration = \App\Models\Registration::where('id_student', $student->id)
                            ->where('id_schoolyear', $schedule->id_schoolyear)
                            ->whereIn('id_class', $schedule->classes->pluck('id'))
                            ->with('class')
                            ->first();

                        if ($registration && $registration->class) {
                            $turmaNome = $registration->class->class;
                            $turmaAno = $registration->class->year;

                            $turmaAlunos[$turmaNome]['ano'] = $turmaAno;
                            $turmaAlunos[$turmaNome]['alunos'][] = "{$student->studentnumber} - {$student->name}";
                        }
                    }
                } else {
                    // Se não há alunos, exporta turmas com campo de turno vazio
                    foreach ($schedule->classes as $class) {
                        $linha = [
                            $schedule->id_weekday,
                            $schedule->id_timeperiod,
                            "\"{$class->class}\"",
                            $class->year,
                            "\"{$schedule->teacher->teachernumber}\"",
                            "\"{$schedule->subject->acronym}\"",
                            "\"{$schedule->room->name}\"",
                            "\"\"", // Turno vazio
                        ];

                        fputs($handle, implode('|', $linha) . "\n");
                    }

                    continue; // Passa para o próximo horário
                }

                // Gera linhas por turma com alunos
                foreach ($turmaAlunos as $turma => $info) {
                    $linha = [
                        $schedule->id_weekday,
                        $schedule->id_timeperiod,
                        "\"$turma\"",
                        $info['ano'],
                        "\"{$schedule->teacher->teachernumber}\"",
                        "\"{$schedule->subject->acronym}\"",
                        "\"{$schedule->room->name}\"",
                        "\"" . implode(' ; ', $info['alunos']) . "\"",
                    ];

                    fputs($handle, implode('|', $linha) . "\n");
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

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

    // public function atualizarEstado($get, $set)
    // {
    //     $sala = $get('id_room');
    //     $inicio = $get('inicio');

    //     if (!$sala || !$inicio) {
    //         $set('estado', null);
    //         return;
    //     }

    //     // Simula uma verificação de conflitos (aqui seria um query real à BD)
    //     $conflitos = Schedule::where('room_id', $sala)
    //         ->where('start', $inicio)
    //         ->exists();

    //     $estado = $conflitos ? 'ocupado' : 'disponível'; // ou 'em conflito', se houver lógica extra
    //     $set('estado', $estado);
    // }

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
                                    ->placeholder('Selecione o local da aula')
                                    ->afterStateHydrated(function (callable $set, ?\App\Models\Schedules $record) {
                                        if ($record && $record->id_room && $record->room) {
                                            $set('building_id', $record->room->building_id);
                                        }
                                    }),

                                Select::make('id_room')
                                    ->label('Sala')
                                    ->required()
                                    ->options(function (callable $get, ?\App\Models\Schedules $record) {
                                        $buildingId = $get('building_id') ?? $record?->room?->building_id;

                                        if (!$buildingId) return [];

                                        return \App\Models\Room::where('building_id', $buildingId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Selecione a sala')
                                    ->reactive()
                                    ->afterStateHydrated(function (callable $set, ?\App\Models\Schedules $record) {
                                        if ($record && $record->id_room) {
                                            $set('id_room', $record->id_room);
                                        }
                                    }),
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
                            ->required(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $subjectName = \App\Models\Subject::find($subjectId)?->subject;

                                return !in_array(strtolower($subjectName), ['reunião', 'tee']);
                            })
                            ->helperText('Selecione a(s) turma(s) que vão assistir à aula')
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, ?Schedules $record) {
                                $set('id_classes', $record?->classes()->pluck('classes.id')->toArray());
                            })
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $buildingId = $get('building_id');

                                if (!$subjectId || !$buildingId) {
                                    return [];
                                }

                                $subject = Subject::find($subjectId);
                                if (!$subject) {
                                    return [];
                                }

                                // Cursos associados à disciplina
                                $courseIds = $subject->courses()->pluck('courses.id');

                                // Turmas associadas ao curso e ao edifício
                                return Classes::whereIn('id_course', $courseIds)
                                    ->where('id_building', $buildingId) // 👈 filtro pelo prédio da turma
                                    ->pluck('class', 'id');
                            }),

                        CheckboxList::make('students')
                            ->label('Alunos matriculados na disciplina')
                            ->helperText('Selecione os alunos que vão assistir à aula')
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, ?\App\Models\Schedules $record) {
                                if ($record) {
                                    $set('students', $record->students()->pluck('students.id')->toArray());
                                }
                            })
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

                                $query = Registration::with('student')
                                    ->whereIn('id', $registrationIds)
                                    ->where('id_schoolyear', $schoolYear->id);

                                if (!empty($classIds)) {
                                    $query->whereIn('id_class', $classIds);
                                }

                                return $query->get()->mapWithKeys(function ($registration) {
                                    $student = $registration->student;
                                    $turma = $registration->class?->class;
                                    if (!$student) return [];

                                    return [
                                        $registration->id_student => "{$turma} - {$student->studentnumber} - {$student->name}",
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
                            //->default('NA')
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
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
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
                TextColumn::make('subject.subject')
                    ->label('Disciplina')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('classes.class')
                    ->label('Turma')
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
            ->headerActions([
                Tables\Actions\Action::make('exportar_selecionados')
                    ->label('Exportar Horários')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn() => self::exportSchedules())
                    ->color('primary')
                    ->requiresConfirmation(),
            ])
            ->actions([
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->after(function ($record) {
                        SchedulesResource::hoursCounterUpdate($record);
                    }),

                // Tables\Actions\EditAction::make()
                //     ->requiresConfirmation()
                //     ->form(fn(Schedules $record) => SchedulesResource::form(Form::make()->model($record)))
                //     ->icon('heroicon-o-pencil-square')
                //     ->color('primary'),

            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function (Collection $records) {
                        foreach ($records as $record) {
                            SchedulesResource::hoursCounterUpdate($record);
                        }
                    }),

                BulkAction::make('exportar_selecionados')
                    ->label('Exportar Selecionados (.txt)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn(Collection $records) => self::exportSchedules($records))
            ]);
    }

    public static function hoursCounterUpdate(Schedules $schedule): void
    {
        $schedule->load('subject');

        $tipo = strtolower(trim($schedule->subject->type ?? 'letiva'));

        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $schedule->id_teacher)->first();
        if (!$counter) {
            Log::warning('Contador de horas não encontrado.', ['id_teacher' => $schedule->id_teacher]);
            return;
        }

        if ($tipo === 'nao letiva') {
            $counter->carga_componente_naoletiva += 1;
            $componente = 'Não Letiva';
        } else {
            $counter->carga_componente_letiva += 1;
            $componente = 'Letiva';
        }

        $counter->carga_horaria = $counter->carga_componente_letiva + $counter->carga_componente_naoletiva;
        $counter->save();

        Log::info('✅ Carga horária reposta após exclusão.', [
            'id_teacher' => $schedule->id_teacher,
            'componente' => $componente,
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
