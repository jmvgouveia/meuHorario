<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleRequestResource\Pages;
use App\Filament\Resources\ScheduleRequestResource\RelationManagers;
use App\Models\ScheduleRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Models\Teacher;
use Filament\Facades\Filament;


class ScheduleRequestResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Horários';
    protected static ?string $navigationLabel = 'Pedidos de Troca';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('justification')
                    ->label('Justificação do Pedido')
                    ->disabled(),

                Textarea::make('response')
                    ->label('Resposta do Professor')
                    ->reactive(),
                /* ->visible(fn($record) => $record->status === 'recusado' || $record->status === 'aprovado_prof')
                    ->required(fn($record) => $record->status === 'recusado') */

                Select::make('status')
                    ->label('Estado do Pedido')
                    ->options([
                        'pendente' => 'Pendente',
                        'recusado' => 'Recusado',
                        'aprovado_prof' => 'Aprovado pelo Professor',
                        'escalado' => 'Escalado para Coordenador',
                        'aprovado_coord' => 'Aprovado pelo Coordenador',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_schedule_conflict')
                    ->label('Conflito')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('id_schedule_novo')
                    ->label('P.Troca')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('scheduleConflict.teacher.name')
                    ->label('Requerente')
                    ->wrap()
                    ->toggleable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('scheduleConflict.room.name')
                    ->label('Sala')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('scheduleConflict.weekday.weekday')
                    ->label('Dia da Semana')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('scheduleConflict.timePeriod.description')
                    ->label('Hora da Aula')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('justification')
                    ->label('Justificação do Pedido')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('response')
                    ->label('Resposta do Professor')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado do Pedido')
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        default => 'gray',
                    })
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
            'index' => Pages\ListScheduleRequests::route('/'),
            'create' => Pages\CreateScheduleRequest::route('/create'),
            'edit' => Pages\EditScheduleRequest::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     // Se for admin, mostra tudo
    //     if (Filament::auth()->user()?->hasRole('admin')) {
    //         return parent::getEloquentQuery();
    //     }

    //     // Se for professor com registo, restringe ao seu ID
    //     $teacher = Teacher::where('id_user', Filament::auth()->id())->first();

    //     if ($teacher) {
    //         return parent::getEloquentQuery()
    //             ->whereHas('scheduleConflict', function ($query) use ($teacher) {
    //                 $query->where('id_teacher', $teacher->id);
    //             });
    //     }

    //     // Caso contrário (sem papel ou associação), não mostra nada
    //     return parent::getEloquentQuery()->whereRaw('1 = 0');
    // }
}
