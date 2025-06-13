<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingResource\Pages;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Imports\BuildingImporter;
use Filament\Actions\ImportAction;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\DateFilter;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Definições Horário';
    protected static ?string $navigationLabel = 'Polos e Núcleos';
    protected static ?int $navigationSort = 5;



    public static function getModelLabel(): string
    {
        return __('filament/resources.buildings.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.buildings.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('name'))
                    ->required()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label(__('address'))
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources.buildings.fields.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('filament/resources.buildings.fields.address'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('rooms_count')
                    ->counts('rooms')
                    ->label(__('filament/resources.buildings.fields.rooms_count'))
                    ->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(BuildingImporter::class)
                    ->label('Importar Edifícios')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
                //Tables\Actions\CreateAction::make(),
            ])
            ->filters([
                // TextFilter::make('name')
                //     ->label(__('filament/resources.buildings.fields.name'))
                //     ->placeholder(__('filament/filters.placeholders.filter', ['resource' => __('filament/resources.buildings.plural')])),
                // TextFilter::make('address')
                //     ->label(__('filament/resources.buildings.fields.address'))
                //     ->placeholder(__('filament/filters.placeholders.filter', ['resource' => __('filament/resources.buildings.plural')])),
                // DateFilter::make('created_at')
                //     ->label(__('filament/resources.buildings.fields.created_at'))
                //     ->placeholder(__('filament/filters.placeholders.filter', ['resource' => __('filament/resources.buildings.plural')])),
                // DateFilter::make('updated_at')
                //     ->label(__('filament/resources.buildings.fields.updated_at'))
                //     ->placeholder(__('filament/filters.placeholders.filter', ['resource' => __('filament/resources.buildings.plural')])),

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

    public static function getRelations(): array
    {
        return [
            BuildingResource\RelationManagers\RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
