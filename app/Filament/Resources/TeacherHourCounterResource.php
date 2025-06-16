<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TeacherHourCounterImporter;
use App\Filament\Resources\TeacherHourCounterResource\Pages;
use App\Filament\Resources\TeacherHourCounterResource\RelationManagers;
use App\Models\TeacherHourCounter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Log;

class TeacherHourCounterResource extends Resource
{
    protected static ?string $model = TeacherHourCounter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Contadores de Horas';

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
                Forms\Components\TextInput::make('carga_horaria')
                    ->label('Carga Horária Total')
                    ->numeric()
                    ->disabled() // para que o usuário não edite diretamente
                    ->reactive()
                    ->dehydrated(true) // para salvar no banco de dados
                    ->afterStateHydrated(function (\Filament\Forms\Components\TextInput $component, $state, $record) {
                        // opcional: garantir que seja calculado ao editar
                        $component->state(
                            ($record->carga_componente_letiva ?? 0) + ($record->carga_componente_naoletiva ?? 0)
                        );
                    }),

                Forms\Components\TextInput::make('carga_componente_letiva')
                    ->label('Carga Horária Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(function (callable $get) {
                        Log::info('Valor de autorizado_horas_extra', ['valor' => $get('autorizado_horas_extra')]);

                        return $get('autorizado_horas_extra') === 'Autorizado' ? 27 : 22;
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('carga_horaria', ($state ?? 0) + ($get('carga_componente_naoletiva') ?? 0));
                    }),

                Forms\Components\TextInput::make('carga_componente_naoletiva')
                    ->label('Carga Horária Não Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('carga_horaria', ($get('carga_componente_letiva') ?? 0) + ($state ?? 0));
                    }),
                Forms\Components\Select::make('autorizado_horas_extra')
                    ->label('Horas Extras Autorizadas')
                    ->options([
                        'Autorizado' => 'Autorizado',
                        'Nao_autorizado' => 'Não autorizado',
                    ])
                    ->default('nao_autorizado') // <- valor compatível com a coluna enum
                    ->helperText('Selecione se o professor está autorizado a realizar horas extras')
                    ->required()

                //
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
                Tables\Columns\TextColumn::make('carga_horaria')
                    ->label('Carga Restante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('carga_componente_letiva')
                    ->label('Carga Horária Letiva')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('carga_componente_naoletiva')
                    ->label('Carga Horária Não Letiva')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('autorizado_horas_extra')
                    ->label('Horas Extras Autorizadas')
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            'autorizado' => 'Autorizado',
                            'nao_autorizado', 'Nao_autorizado' => 'Não Autorizado',
                            default => ucfirst($state ?? '-'),
                        };
                    })
                    ->color(fn(?string $state): string => in_array($state, ['autorizado', 'Autorizado']) ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TeacherHourCounterImporter::class)
                    ->label('Import Teacher Hours')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherHourCounters::route('/'),
            'create' => Pages\CreateTeacherHourCounter::route('/create'),
            'edit' => Pages\EditTeacherHourCounter::route('/{record}/edit'),
        ];
    }
}
