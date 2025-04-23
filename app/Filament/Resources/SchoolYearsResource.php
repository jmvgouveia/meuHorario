<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolYearsResource\Pages;
use App\Filament\Resources\SchoolYearsResource\RelationManagers;
use App\Models\SchoolYears;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

use function Laravel\Prompts\select;

class SchoolYearsResource extends Resource
{
    protected static ?string $model = SchoolYears::class;
    protected static ?string $navigationGroup = 'Definições Horário';

    protected static ?string $navigationLabel = 'Ano Lectivo';
    protected static ?string $label = 'Ano Lectivo';
    protected static ?string $pluralLabel = 'Anos Lectivos';
    protected static ?string $slug = 'school-years';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('schoolyear')
                    ->label('Ano Lectivo')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ano Lectivo')
                    ->helperText('Introduza o ano lectivo'),
                Select::make('active')
                    ->label('Estado')
                    ->required()
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ])
                    ->placeholder('Estado')
                    ->helperText('Introduza o estado'),
                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->placeholder('Data de Início')
                    ->helperText('Introduza a data de início'),
                DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->placeholder('Data de Fim')
                    ->helperText('Introduza a data de fim'),

                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolyear')
                    ->label('Ano Lectivo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('active')
                    ->label('Estado')
                    ->sortable()

                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? 'Ativo' : 'Inativo'),

                TextColumn::make('start_date')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('end_date')
                    ->label('Data de Fim')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListSchoolYears::route('/'),
            'create' => Pages\CreateSchoolYears::route('/create'),
            'edit' => Pages\EditSchoolYears::route('/{record}/edit'),
        ];
    }
}
