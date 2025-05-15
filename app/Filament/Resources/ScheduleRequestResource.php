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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('justification')
                    ->label('Justificação do Pedido')
                    ->disabled(),

                Textarea::make('response')
                    ->label('Resposta do Professor')
                    ->visible(fn($record) => $record->status === 'recusado' || $record->status === 'aprovado_prof')
                    ->required(fn($record) => $record->status === 'recusado')
                    ->reactive(),

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

    public static function getEloquentQuery(): Builder
    {
        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();

        return parent::getEloquentQuery()
            ->whereHas(
                'scheduleConflict',
                fn($query) =>
                $query->where('id_teacher', $teacher->id)
            );
    }
}
