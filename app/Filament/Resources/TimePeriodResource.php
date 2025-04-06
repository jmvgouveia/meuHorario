<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimePeriodResource\Pages;
use App\Models\TimePeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Imports\TimePeriodImporter;
use Filament\Actions\ImportAction;

class TimePeriodResource extends Resource
{
    protected static ?string $model = TimePeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Definições Horários';

    public static function getModelLabel(): string
    {
        return __('filament/resources.time_periods.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.time_periods.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label(__('filament/resources.time_periods.fields.description'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder('08:00 - 09:00'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament/resources.time_periods.fields.description'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TimePeriodImporter::class),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimePeriods::route('/'),
            'create' => Pages\CreateTimePeriod::route('/create'),
            'edit' => Pages\EditTimePeriod::route('/{record}/edit'),
        ];
    }
}
