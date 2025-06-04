<?php

namespace App\Filament\Resources;

//use App\Filament\Resources\ScheduleRequestResolveConflitResource\Pages;
use App\Filament\Resources\ScheduleRequestResolveConflict\Pages;
use App\Models\ScheduleRequest;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\View;


class ScheduleRequestResolveConflict extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationLabel = 'Conflitos de Horário';
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Horários';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('🟢 Passo 1: Marcação original')
                ->description('O professor que fez a marcação inicial no horário.')
                ->schema([
                    Placeholder::make('professor_original')
                        ->label('Marcado por:')
                        ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),

                    Placeholder::make('sala')
                        ->label('Sala')
                        ->content(fn($record) => $record->scheduleConflict->room->name ?? '—'),
                    Placeholder::make('dia')
                        ->label('Dia da Semana')
                        ->content(fn($record) => $record->scheduleConflict->weekday->weekday ?? '—'),

                    Placeholder::make('hora')
                        ->label('Hora')
                        ->content(fn($record) => $record->scheduleConflict->timePeriod->description ?? '—'),

                ])
                ->columns(2),

            Section::make('🟡 Passo 2: Pedido de alteração')
                ->description('Solicitação feita por outro professor.')
                ->schema([
                    Placeholder::make('solicitante')
                        ->label('Pedido feito por:')
                        ->content(fn($record) => $record->requester->name ?? '—'),

                    Textarea::make('justification')
                        ->label('Justificação do Pedido')
                        ->disabled(),

                    TextInput::make('created_at')
                        ->label('Data do Pedido')
                        ->disabled(),
                ])
                ->columns(1),

            Section::make('🔵 Passo 3: Resposta do professor original')
                ->description('Resposta ao pedido.')
                ->schema([
                    Placeholder::make('professor_original')
                        ->label('Resposta de:')
                        ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),
                    Textarea::make('response')
                        ->label('')
                        ->disabled(),

                    TextInput::make('responded_at')
                        ->label('Data da Resposta')
                        ->disabled(),
                ])
                ->columns(1),

            Section::make('🔴 Passo 4: Escalada para Direção Pedagógica')
                ->description('Situação escalada para análise superior.')
                ->schema([
                    TextInput::make('status')
                        ->label('Estado Atual')
                        ->disabled(),

                    Placeholder::make('escalada')
                        ->content(
                            fn($record) => $record->status === 'Escalado'
                                ? 'Este pedido foi escalado para a Direção Pedagógica.'
                                : 'Este pedido ainda não foi escalado.'
                        ),
                ])
                ->columns(1),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // 1. Quem fez a marcação original (professor com conflito)
                TextColumn::make('scheduleConflict.teacher.name')
                    ->label('Professor com Marcações')
                    ->sortable()
                    ->searchable(),

                // 2. Quem fez o pedido
                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->sortable()
                    ->searchable(),

                // 3. Sala
                TextColumn::make('scheduleConflict.room.name')
                    ->label('Sala')
                    ->sortable()
                    ->searchable(),

                // 4. Hora
                TextColumn::make('scheduleConflict.timePeriod.description')
                    ->label('Hora')
                    ->sortable(),

                // 5. Dia da semana
                TextColumn::make('scheduleConflict.weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable(),

                // 6. Estado
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ]) // vamos preencher depois
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduleRequestConflicts::route('/'),
            'create' => Pages\CreateScheduleRequestResolveConflict::route('/create'),
            'edit' => Pages\EditScheduleRequestResolveConflict::route('/{record}/edit'),
        ];
    }
}
