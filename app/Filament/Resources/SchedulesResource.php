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

class SchedulesResource extends Resource
{
    protected static ?string $model = Schedules::class;

    protected static ?string $navigationGroup = 'Horários';
    protected static ?string $navigationLabel = 'Marcação de Horários';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



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


                Select::make('building_id')
                    ->label('Núcleo ou Polo')
                    ->required()
                    ->options(Building::all()->pluck('name', 'id'))
                    ->reactive() // muito importante para reatividade
                    ->afterStateUpdated(function (callable $set) {
                        // Limpa o campo de sala quando o prédio for alterado
                        $set('id_room', null);
                    })
                    ->placeholder('Selecione o local da aula'),

                Select::make('id_room')
                    ->label('Sala')
                    ->required()
                    ->options(function (callable $get) {
                        $buildingId = $get('building_id');
                        if (!$buildingId) {
                            return [];
                        }

                        return Room::where('building_id', $buildingId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Selecione a sala')
                    ->reactive(),
                //  ->afterStateUpdated(function (callable $get, callable $set) {
                //   self::atualizarEstado($get, $set);
                //    }),


                Select::make('id_weekday')
                    ->label('Dia da Semana')
                    ->required()
                    ->options(WeekDays::all()->pluck('weekday', 'id'))
                    ->placeholder('Selecione o dia da semana'),
                Select::make('id_timeperiod')
                    ->label('Hora de Início')
                    ->required()
                    ->placeholder('Selecione a hora de inicio da aula')
                    ->options(TimePeriod::all()->pluck('description', 'id'))
                    ->reactive(),
                //   ->afterStateUpdated(fn($get, $set) => atualizarEstado($get, $set)),
                Select::make('id_subject')
                    ->label('Disciplina')
                    ->required()
                    ->searchable()
                    ->options(Subject::all()->pluck('subject', 'id'))
                    ->placeholder('Escolha a disciplina'),

                Select::make('id_subject')
                    ->label('Turma')
                    ->required()
                    ->options(Classes::all()->pluck('class', 'id'))
                    ->placeholder('Escolha a Turma'),

                ColorPicker::make('estado_cor')
                    ->label('Indicador de Estado')
                    ->disabled()
                    ->visible(fn($get) => filled($get('estado')))
                    ->default(function ($get) {
                        return match ($get('estado')) {
                            'disponível' => '#16a34a',   // verde
                            'ocupado' => '#dc2626',      // vermelho
                            'em conflito' => '#eab308',  // amarelo
                            default => '#9ca3af',        // cinzento
                        };
                    }),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('id_weekday')
                    ->label('Dia da Semana')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('id_timeperiod')
                    ->label('Hora de Início')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('id_timeperiod')
                    ->label('Hora de Início')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('id_room')
                    ->label('Sala')
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedules::route('/create'),
            'edit' => Pages\EditSchedules::route('/{record}/edit'),
        ];
    }
}
