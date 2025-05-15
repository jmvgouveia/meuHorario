<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherSubjectsResource\Pages;
use App\Filament\Resources\TeacherSubjectsResource\RelationManagers;
use App\Models\TeacherSubjects;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;

class TeacherSubjectsResource extends Resource
{
    protected static ?string $model = TeacherSubjects::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gestão de Cursos';
    protected static ?string $navigationLabel = 'Professor e Disciplina';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_teacher')
                    ->label('Professor')
                    ->required()
                    ->relationship('teacher', 'name')
                    ->placeholder('Selecione o professor')
                    ->helperText('Selecione o professor'),
                Select::make('id_subject')
                    ->label('Disciplina')
                    ->required()
                    ->relationship('subject', 'subject')
                    ->placeholder('Selecione a disciplina')
                    ->helperText('Selecione a disciplina'),
                Select::make('id_schoolyear')
                    ->label('Ano Lectivo')
                    ->required()
                    ->relationship('schoolyear', 'schoolyear')
                    ->placeholder('Selecione o ano lectivo')
                    ->helperText('Selecione o ano lectivo'),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('Professor')

                    ->sortable()
                    ->searchable(),
                TextColumn::make('subject.subject')
                    ->label('Disciplina')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Lectivo')
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
            'index' => Pages\ListTeacherSubjects::route('/'),
            'create' => Pages\CreateTeacherSubjects::route('/create'),
            'edit' => Pages\EditTeacherSubjects::route('/{record}/edit'),
        ];
    }
}
