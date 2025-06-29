<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Filament\Resources\PositionResource\RelationManagers;
use App\Models\Position;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

use function Laravel\Prompts\text;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;
    protected static ?string $navigationGroup = 'Definições Professor';
    protected static ?string $navigationLabel = 'Cargo';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 12;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                textInput::make('position')
                    ->label('Cargo')
                    ->required()
                    ->maxLength(255)
                    ->required()
                    ->placeholder('Cargo')
                    ->helperText('Informe o cargo'),

                TextInput::make('position_description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Descrição')
                    ->helperText('Informe a descrição do cargo'),

                TextInput::make('position_reduction_value')
                    ->label('Valor da Redução Letiva')
                    ->required()
                    ->maxLength(11)
                    ->placeholder('1 hora')
                    ->helperText('Informe o valor da redução em horas'),
                TextInput::make('position_reduction_value_nl')
                    ->label('Valor da Redução Não Letiva')
                    ->required()
                    ->maxLength(11)
                    ->placeholder('1 hora')
                    ->helperText('Informe o valor da redução em horas'),

                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Cargo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('position_description')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('position_reduction_value')
                    ->label('Valor da Redução Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('position_reduction_value_nl')
                    ->label('Valor da Redução Não Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
               // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
