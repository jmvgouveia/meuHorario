<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherPositionResource\Pages;
use App\Filament\Resources\TeacherPositionResource\RelationManagers;
use App\Models\TeacherPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Models\TeacherHourCounter;


class TeacherPositionResource extends Resource
{
    protected static ?string $model = TeacherPosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_teacher')
                    ->label('Professor')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->reactive()
                    ->placeholder('Selecione um professor'),
                Select::make('id_position')
                    ->label('Cargo')
                    ->relationship('position', 'position')
                    ->required()
                    ->reactive(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Professor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_description')
                    ->label('Descrição do Cargo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_reduction_value')
                    ->label('Redução Letiva')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.position_reduction_value_nl')
                    ->label('Redução Não Letiva')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListTeacherPositions::route('/'),
            'create' => Pages\CreateTeacherPosition::route('/create'),
            'edit' => Pages\EditTeacherPosition::route('/{record}/edit'),
        ];
    }
}
