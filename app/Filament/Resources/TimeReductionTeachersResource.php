<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeReductionTeachersResource\Pages;
use App\Filament\Resources\TimeReductionTeachersResource\RelationManagers;
use App\Models\TimeReductionTeachers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Log;

class TimeReductionTeachersResource extends Resource
{
    protected static ?string $model = TimeReductionTeachers::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    // protected static ?string $navigationGroup = 'Definições Horário';
    protected static ?string $navigationLabel = 'Reduções';
    protected static ?int $navigationSort = 5;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_teacher')
                    ->label('Professor')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->reactive()
                    ->placeholder('Selecione um professor'),

                Forms\Components\Select::make('id_time_reduction')
                    ->label('Redução de Tempo')
                    ->relationship('timeReduction', 'time_reduction')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $teacherId = $get('id_teacher');
                        $reductionId = $get('id_time_reduction');
                        // $teacher = \App\Models\Teacher::with('genders')->find($teacherId);

                        // dd($teacher->genders->getAttributes());

                        if ($teacherId && $reductionId) {
                            $teacher = \App\Models\Teacher::with('genders')->find($teacherId);
                            $reduction = \App\Models\TimeReduction::find($reductionId);

                            if ($teacher && $reduction && $teacher->genders) {
                                $teacherGender = strtolower(trim($teacher->genders->gender ?? ''));
                                $eligibility = strtolower(trim($reduction->eligibility ?? ''));

                                if (empty($teacherGender)) {
                                    Notification::make()
                                        ->title('Género não definido')
                                        ->body('O professor não tem género associado. Não é possível verificar elegibilidade da redução.')
                                        ->danger()
                                        ->persistent()
                                        ->send();

                                    $set('id_time_reduction', null);
                                    return;
                                }

                                if ($eligibility !== 'ambos' && $eligibility !== $teacherGender) {
                                    $set('id_time_reduction', null);

                                    Notification::make()
                                        ->title('Redução não permitida')
                                        ->body('Esta redução de tempo não é elegível para o género do professor.')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                }
                            }
                        }
                    })

                    ->placeholder('Selecione uma redução de tempo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Professor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction')
                    ->label('Redução de Tempo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_description')
                    ->label('Descrição da Redução')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_value')
                    ->label('Componente Letivo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_value_nl')
                    ->label('Componente Não Letivo')
                    ->searchable()
                    ->sortable()
                //  ->formatStateUsing(fn ($state) => $state === 'fixed' ? 'Fixo' : 'Percentual'),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        Log::info('AFTER DELETE (linha única) acionado', ['id' => $record->id]);

                        $record->load('timeReduction');

                        $reduction = $record->timeReduction;
                        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                        if (!$reduction) {
                            Log::warning('Redução não carregada no DeleteAction.');
                            return;
                        }

                        if (!$counter) {
                            Log::warning('Counter não encontrado para professor.', ['id_teacher' => $record->id_teacher]);
                            return;
                        }

                        $valorLetiva = floatval($reduction->time_reduction_value ?? 0);
                        $valorNaoLetiva = floatval($reduction->time_reduction_value_nl ?? 0);

                        Log::info('Reposição individual', [
                            'letiva' => $valorLetiva,
                            'naoletiva' => $valorNaoLetiva,
                        ]);

                        $novaLetiva = $counter->carga_componente_letiva + $valorLetiva;
                        $novaNaoLetiva = $counter->carga_componente_naoletiva + $valorNaoLetiva;

                        $counter->carga_componente_letiva = $novaLetiva;
                        $counter->carga_componente_naoletiva = $novaNaoLetiva;
                        $counter->carga_horaria = $novaLetiva + $novaNaoLetiva;
                        $counter->save();

                        Log::info('Reposição concluída individualmente.');
                    })
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($records) {
                        foreach ($records as $record) {
                            $record->load('timeReduction');

                            $reduction = $record->timeReduction;
                            $counter = \App\Models\TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                            if ($reduction && $counter) {
                                $valorLetiva = floatval($reduction->time_reduction_value ?? 0);
                                $valorNaoLetiva = floatval($reduction->time_reduction_value_nl ?? 0);

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
            'index' => Pages\ListTimeReductionTeachers::route('/'),
            'create' => Pages\CreateTimeReductionTeachers::route('/create'),
            'edit' => Pages\EditTimeReductionTeachers::route('/{record}/edit'),
        ];
    }
}
