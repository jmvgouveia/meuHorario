<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherPositionResource\Pages;
use App\Filament\Resources\TeacherPositionResource\RelationManagers;
use App\Models\TeacherPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Models\TeacherHourCounter;
use Illuminate\Support\Facades\Log;


class TeacherPositionResource extends Resource
{
    protected static ?string $model = TeacherPosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Cargos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_teacher')
                    ->label('Professor')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->reactive()
                    ->placeholder('Selecione um professor'),
                Select::make('id_position')
                    ->label('Cargo')
                    ->relationship('position', 'position')
                    ->required()
                    ->reactive(),
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
                Tables\Columns\TextColumn::make('position.position')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_description')
                    ->label('Descrição do Cargo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_reduction_value')
                    ->label('Redução Letiva')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_reduction_value_nl')
                    ->label('Redução Não Letiva')
                    ->searchable()
                    ->sortable(),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        Log::info('AFTER DELETE (linha única) acionado', ['id' => $record->id]);

                        $reduction = $record->position; // CORRIGIDO AQUI
                        $counter = \App\Models\TeacherHourCounter::where('id_teacher', $record->id_teacher)->first();

                        if (!$reduction) {
                            Log::warning('Cargo (position) não carregado no DeleteAction.');
                            return;
                        }

                        if (!$counter) {
                            Log::warning('Counter não encontrado para professor.', ['id_teacher' => $record->id_teacher]);
                            return;
                        }

                        $valorLetiva = floatval($reduction->position_reduction_value ?? 0);
                        $valorNaoLetiva = floatval($reduction->position_reduction_value_nl ?? 0);

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
            'index' => Pages\ListTeacherPositions::route('/'),
            'create' => Pages\CreateTeacherPosition::route('/create'),
            'edit' => Pages\EditTeacherPosition::route('/{record}/edit'),
        ];
    }
}
