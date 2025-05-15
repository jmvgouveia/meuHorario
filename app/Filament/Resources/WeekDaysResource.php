<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeekDaysResource\Pages;
use App\Filament\Resources\WeekDaysResource\RelationManagers;
use App\Models\WeekDays;
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

class WeekDaysResource extends Resource
{
    protected static ?string $model = WeekDays::class;

    protected static ?string $navigationGroup = 'Definições Horário';
    protected static ?string $navigationLabel = 'Dias da Semana';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('weekday')
                    ->label('Dia da semana')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Dia da semana')
                    ->helperText('Informe o dia da semana'),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('weekday')
                    ->label('Dia da semana')
                    ->sortable()
                    ->searchable(),
                //
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
            'index' => Pages\ListWeekDays::route('/'),
            'create' => Pages\CreateWeekDays::route('/create'),
            'edit' => Pages\EditWeekDays::route('/{record}/edit'),
        ];
    }
}
