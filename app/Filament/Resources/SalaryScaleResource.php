<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryScaleResource\Pages;
use App\Filament\Resources\SalaryScaleResource\RelationManagers;
use App\Models\SalaryScale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class SalaryScaleResource extends Resource
{
    protected static ?string $model = SalaryScale::class;
    protected static ?string $navigationGroup = 'Definições Professor';
    protected static ?string $navigationLabel = 'Escalão Salarial';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('scale')
                    ->label('Escalão Salarial')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Escalão Salarial')
                    ->helperText('Informe o escalão salarial'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('scale')
                    ->label('Escalão Salarial')
                    ->sortable()
                    ->searchable()
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
            'index' => Pages\ListSalaryScales::route('/'),
            'create' => Pages\CreateSalaryScale::route('/create'),
            'edit' => Pages\EditSalaryScale::route('/{record}/edit'),
        ];
    }
}
