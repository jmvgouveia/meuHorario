<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeReductionResource\Pages;
use App\Filament\Resources\TimeReductionResource\RelationManagers;
use App\Models\TimeReduction;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\Select;

class TimeReductionResource extends Resource
{
    protected static ?string $model = TimeReduction::class;
    protected static ?string $navigationGroup = 'Definições Professor';
    protected static ?string $navigationLabel = 'Redução de Horário';
    protected static ?int $navigationSort = 17;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('time_reduction')
                    ->label('Redução de Horário')
                    ->required()
                    ->helperText('Informe a redução de horário'),
                TextInput::make('time_reduction_description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Informe a descrição'),
                TextInput::make('time_reduction_value')
                    ->label('Valor da Redução Letiva')
                    ->required()
                    ->maxLength(11)
                    ->placeholder('1 hora')
                    ->helperText('Informe o valor da redução em horas'),
               TextInput::make('time_reduction_value_nl')
                    ->label('Valor da Redução Não Letiva')
                    ->required()
                    ->maxLength(11)
                    ->placeholder('1 hora')
                    ->helperText('Informe o valor da redução em horas'),
                Select::make('eligibility')
                    ->label('Elegibilidade')
                    ->options([
                        'Ambos' => 'Ambos',
                        'Masculino' => 'Masculino',
                        'Feminino' => 'Feminino',
                    ])
                   //->default('Sim')
                    ->helperText('Selecione se a redução é elegível para professores'),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('time_reduction')
                    ->label('Redução de Horário')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('time_reduction_description')
                    ->label('Descrição')
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('time_reduction_value')
                    ->label('Redução Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('time_reduction_value_nl')
                    ->label('Redução Não Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('eligibility') 
                    ->label('Elegibilidade')
                    ->badge()
    ->color(fn (string $state): string => match (strtolower($state)) {
       'feminino' => 'danger',     // vermelho (ou usa 'purple' para algo mais suave)
    'masculino' => 'info',   // azul
    'ambos' => 'gray', // azul claro
    })

                    ->sortable(),
                    

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
            'index' => Pages\ListTimeReductions::route('/'),
            'create' => Pages\CreateTimeReduction::route('/create'),
            'edit' => Pages\EditTimeReduction::route('/{record}/edit'),
        ];
    }
}
