<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeReductionTeachersResource\Pages;
use App\Filament\Resources\TimeReductionTeachersResource\RelationManagers;
use App\Models\TimeReductionTeachers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
   use Filament\Notifications\Notification;


class TimeReductionTeachersResource extends Resource
{
    protected static ?string $model = TimeReductionTeachers::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
   // protected static ?string $navigationGroup = 'Definições Horário';
   // protected static ?string $navigationLabel = 'Redução de Tempo - Professores';
    protected static ?int $navigationSort = 5;




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Forms\Components\Select::make('id_teacher')
    ->label('Professor')
    ->relationship('teacher', 'name')
    ->required()
    ->reactive()
    ->placeholder('Selecione um professor'),

Forms\Components\Select::make('id_time_reduction')
    ->label('Redução de Tempo')
    ->relationship('timeReduction', 'time_reduction')
    ->required()
    ->reactive()
    ->afterStateUpdated(function (callable $get, callable $set) {
        $teacherId = $get('id_teacher');
        $reductionId = $get('id_time_reduction');

        if ($teacherId && $reductionId) {
            $teacher = \App\Models\Teacher::find($teacherId);
            $reduction = \App\Models\TimeReduction::find($reductionId);

            if ($teacher && $reduction) {
                $teacherGender = strtolower($teacher->gender);
                $eligibility = strtolower($reduction->eligibility);

                if ($eligibility !== 'ambos' && $eligibility !== $teacherGender) {
                    $set('id_time_reduction', null); // Reseta campo inválido

                    Notification::make()
                        ->title('Redução não permitida')
                        ->body("Esta redução de tempo não é elegível para o género do professor.")
                        ->danger()
                        ->persistent()
                        ->send();
                }
            }
        }
    })
    ->placeholder('Selecione uma redução de tempo'),
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
                Tables\Columns\TextColumn::make('timeReduction.time_reduction')
                    ->label('Redução de Tempo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_description')
                    ->label('Descrição da Redução')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_value')
                    ->label('Componente Letivo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeReduction.time_reduction_value_nl')
                    ->label('Componente Não Letivo')
                    ->searchable()
                    ->sortable()
                  //  ->formatStateUsing(fn ($state) => $state === 'fixed' ? 'Fixo' : 'Percentual'),
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
            'index' => Pages\ListTimeReductionTeachers::route('/'),
            'create' => Pages\CreateTimeReductionTeachers::route('/create'),
            'edit' => Pages\EditTimeReductionTeachers::route('/{record}/edit'),
        ];
    }
}
