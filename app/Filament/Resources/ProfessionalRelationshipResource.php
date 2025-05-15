<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessionalRelationshipResource\Pages;
use App\Filament\Resources\ProfessionalRelationshipResource\RelationManagers;
use App\Models\ProfessionalRelationship;
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

class ProfessionalRelationshipResource extends Resource
{
    protected static ?string $model = ProfessionalRelationship::class;
    protected static ?string $navigationGroup = 'Definições Professor';
    protected static ?string $navigationLabel = 'Relação Profissional';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 15;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('professional_relationship')
                    ->label('Relação Profissional')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Relação Profissional')
                    ->helperText('Informe a relação profissional'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('professional_relationship')
                    ->label('Relação Profissional')
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
            'index' => Pages\ListProfessionalRelationships::route('/'),
            'create' => Pages\CreateProfessionalRelationship::route('/create'),
            'edit' => Pages\EditProfessionalRelationship::route('/{record}/edit'),
        ];
    }
}
