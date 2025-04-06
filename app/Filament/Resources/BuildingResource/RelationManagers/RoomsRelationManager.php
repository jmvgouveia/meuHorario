<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;


class RoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'rooms';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources.rooms.plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament/resources.rooms.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('capacity')
                    ->label(__('filament/resources.rooms.fields.capacity'))
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources.rooms.fields.name'))
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label(__('filament/resources.rooms.fields.capacity'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
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
}
