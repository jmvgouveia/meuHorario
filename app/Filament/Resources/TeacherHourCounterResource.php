<?php

namespace App\Filament\Resources;

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

class TeacherHourCounterResource extends Resource
{
    protected static ?string $model = TeacherHourCounter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->label('Carga Horária')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(22)
                    ->placeholder('Informe a carga horária')
                    ->helperText('Informe a carga horária total do professor'),
                Forms\Components\TextInput::make('carga_componente_letiva')
                    ->label('Carga Horária Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1000)
                    ->placeholder('Informe a carga horária letiva')
                    ->helperText('Informe a carga horária letiva do professor'),
                Forms\Components\TextInput::make('carga_componente_naoletiva')        
                    ->label('Carga Horária Não Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(2)
                    ->placeholder('Informe a carga horária não letiva')
                    ->helperText('Informe a carga horária não letiva do professor'),
                Forms\Components\Select::make('autorizado_horas_extra')
                    ->label('Horas Extras Autorizadas')
                    ->options([
                        'autorizado' => 'Autorizado',
                        'nao_autorizado' => 'Não autorizado',
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
                    ->label('Carga Horária')
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
                   // ->formatStateUsing(fn (bool $state): string => $state ? 'Autorizado' : 'Não autorizado')
                   // ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListTeacherHourCounters::route('/'),
            'create' => Pages\CreateTeacherHourCounter::route('/create'),
            'edit' => Pages\EditTeacherHourCounter::route('/{record}/edit'),
        ];
    }
}
