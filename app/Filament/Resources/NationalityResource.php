<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NationalityResource\Pages;
use App\Filament\Resources\NationalityResource\RelationManagers;
use App\Models\Nationality;
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

class NationalityResource extends Resource
{
    protected static ?string $model = Nationality::class;
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Nacionalidade';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                textInput::make('nationality')
                ->label('Nacionalidade')
                ->required()
                ->maxLength(255)
                ->required()
                ->placeholder('Nacionalidade')
                ->helperText('Introduza a nacioalidade'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                ->label('ID')
                ->searchable()
                ->sortable(),
            TextColumn::make('nationality')
                ->label('Nacionalidade')
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
            'index' => Pages\ListNationalities::route('/'),
            'create' => Pages\CreateNationality::route('/create'),
            'edit' => Pages\EditNationality::route('/{record}/edit'),
        ];
    }
}
