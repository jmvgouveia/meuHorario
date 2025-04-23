<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractualRelationshipResource\Pages;
use App\Filament\Resources\ContractualRelationshipResource\RelationManagers;
use App\Models\ContractualRelationship;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractualRelationshipResource extends Resource
{
    protected static ?string $model = ContractualRelationship::class;

    protected static ?string $navigationGroup = 'Definições Professor';
    protected static ?string $navigationLabel = 'Relação Contratual';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 14;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('contractual_relationship')
                    ->label('Relação Contratual')
                    ->required()
                    ->maxLength(255)
                    ->required()
                    ->placeholder('Relação Contratual')
                    ->helperText('Informe a relação contratual')
                //
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
                TextColumn::make('contractual_relationship')
                    ->label('Relação Contratual')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
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
            'index' => Pages\ListContractualRelationships::route('/'),
            'create' => Pages\CreateContractualRelationship::route('/create'),
            'edit' => Pages\EditContractualRelationship::route('/{record}/edit'),
        ];
    }
}
