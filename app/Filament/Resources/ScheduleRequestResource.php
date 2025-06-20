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
use Filament\Tables\Filters\TabsFilter;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;

use Filament\Forms\Components\Grid;
use App\Models\Building;
use App\Models\Room;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\View;
use Closure;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\MultiSelectFilter;

use Filament\Tables\Columns\TextColumn;





class ScheduleRequestResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Horários';
    protected static ?string $navigationLabel = 'Pedidos de Troca';

    public static function getEloquentQuery(): Builder
    {
        $userId = Filament::auth()->id();
    $user = Filament::auth()->user();


          // ✅ Se for um gestor (por ID ou por papel), vê tudo
    if (in_array($user?->id, [1]) || $user?->hasRole('admin')) {
        return parent::getEloquentQuery();
    }

        $teacher = \App\Models\Teacher::where('id_user', $userId)->first();

        return parent::getEloquentQuery()
            ->where(function ($query) use ($teacher) {
                $query
                    ->where('id_teacher_requester', $teacher?->id)
                    ->orWhereHas('scheduleConflict', function ($subQuery) use ($teacher) {
                        $subQuery->where('id_teacher', $teacher?->id);
                    });
            });
    }


    public static function form(Form $form): Form
    {
        return $form


            ->schema([
                Section::make('Pedido de Troca de Horário')
                    ->description('Preencha os campos abaixo para solicitar uma troca de horário.')
                    ->columns(1)
                    ->schema([
                        Select::make('id_teacher_requester')
                            ->label(fn($get) => $get('status') === 'Recusado' ? 'Recusado por' : 'Requerente')
                            ->relationship('requester', 'name')
                            ->default(function ($record) {
                                if ($record?->status === 'Recusado') {
                                    return $record->scheduleConflict?->teacher?->id; // professor original
                                }

                                return Filament::auth()->user()->teacher?->id;
                            })
                            ->disabled()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado do Pedido')
                            ->options([
                                'Pendente' => 'Pendente',
                                'Aprovado' => 'Aprovado',
                                'Recusado' => 'Recusado',
                                'Escalado' => 'Escalado',
                                'Aprovado DP' => 'Aprovado DP',
                                'Recusado DP' => 'Recusado DP',
                            ])
                            ->required()
                            ->reactive() // importante para reatividade
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('justification')
                            ->label('Justificação do Pedido')
                            ->visible(fn($get) => $get('status') === 'Pendente')
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('response')
                            ->label('Motivo da Recusa')
                            ->visible(fn($get) => $get('status') === 'Recusado')
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('response_coord')
                            ->label('Notas de Aprovação')
                            ->visible(fn($get) => $get('status') === 'Aprovado')
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('justification_escalation')
                            ->label('Notas de Aprovação')
                            ->visible(fn($get) => $get('status') === 'Escalado')
                            ->disabled()
                            ->columnSpanFull(),


                    ]),



            ]);
    }

    public static function table(Table $table): Table
    {   
            $userId = Filament::auth()->id();
           $isGestor = in_array($userId, [1]); // ou usa uma função global/policy

        $columns = [
           TextColumn::make('requester.name')
                    ->label('Requerente')
                    ->wrap()
                    ->toggleable()
                    ->limit(25),
           
           TextColumn::make('scheduleConflict.weekday.weekday')
                    ->label('Dia da Semana')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
           TextColumn::make('scheduleConflict.timePeriod.description')
                    ->label('Hora da Aula')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
            TextColumn::make('scheduleConflict.room.name')
                    ->label('Sala')
                    ->toggleable()
                    ->limit(50),
           TextColumn::make('justification')
                    ->label('Justificação do Pedido')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('status')
                ->label('Estado do Pedido')
                ->toggleable()
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Pendente' => 'warning',
                    'Aprovado' => 'success',
                    'Recusado' => 'danger',
                    'Escalado' => 'info',
                    'Aprovado DP' => 'success',
                    'Recusado DP' => 'danger',
                    default => 'gray',
                })
                ->sortable()
                

        ];

        if ($isGestor) {
            $columns = array_merge($columns, [
            TextColumn::make('scheduleConflict.teacher.name')
            ->label('Professor Original')
            ->wrap()
            ->toggleable()
            ->limit(50),
            TextColumn::make('response')
                ->label('Resposta do Professor')
                ->wrap()
                ->toggleable()
                ->limit(50),
            TextColumn::make('created_at')
                ->label('Data de Criação')
                ->dateTime()
                ->toggleable()
                ->sortable(),
            TextColumn::make('updated_at')
                ->label('Data de Atualização')
                ->dateTime()
                ->toggleable()
                ->sortable(),
            TextColumn::make('id')
                ->label('ID do Pedido')
                ->toggleable()
                ->sortable(),    
                       
            ]);
   
        }

return $table
        ->columns($columns)
        ->filters([
            MultiSelectFilter::make('status')
                ->options([
                     'Pendente' => 'Pendente',
                    'Aprovado' => 'Aprovado',
                    'Recusado' => 'Recusado',
                    'Escalado' => 'Escalado',
                    'Aprovado DP' => 'Aprovado DP',
                    'Recusado DP' => 'Recusado DP',
                ])
                ->label('Estado'),

            ...( $isGestor ? [
                MultiSelectFilter::make('id_teacher_requester')
                    ->relationship('requester', 'name')
                    ->label('Requerente'),
                
                // SelectFilter::make('id_schedule_conflict')
                //     ->relationship('scheduleConflict.teacher.user', 'name')
                //     ->label('Alvo do Pedido'),
            ] : []),
        ])
       ->actions([
               //  Tables\Actions\ViewAction::make(),
               //  Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
            ],
    );












        // return $table
        //     ->columns([
        //         // Tables\Columns\TextColumn::make('id_schedule_conflict')
        //         //     ->label('Conflito')
        //         //     ->wrap()
        //         //     ->toggleable()
        //         //     ->limit(50),
        //         // Tables\Columns\TextColumn::make('id_schedule_novo')
        //         //     ->label('P.Troca')
        //         //     ->toggleable()
        //         //     ->limit(50),
        //         Tables\Columns\TextColumn::make('requester.name')
        //             ->label('Requerente')
        //             ->wrap()
        //             ->toggleable()
        //             ->limit(25),
        //         Tables\Columns\TextColumn::make('scheduleConflict.room.name')
        //             ->label('Sala')
        //             ->toggleable()
        //             ->limit(50),
        //         Tables\Columns\TextColumn::make('scheduleConflict.weekday.weekday')
        //             ->label('Dia da Semana')
        //             ->wrap()
        //             ->toggleable()
        //             ->limit(50),
        //         Tables\Columns\TextColumn::make('scheduleConflict.timePeriod.description')
        //             ->label('Hora da Aula')
        //             ->wrap()
        //             ->toggleable()
        //             ->limit(50),
        //         Tables\Columns\TextColumn::make('justification')
        //             ->label('Justificação do Pedido')
        //             ->wrap()
        //             ->toggleable()
        //             ->limit(50),
        //             ($isGestor ? [
                 
        //             Tables\Columns\TextColumn::make('response_coord')
        //                 ->label('Resposta do Coordenador')
        //                 ->wrap()
        //                 ->toggleable()
        //                 ->limit(50),
        //             Tables\Columns\TextColumn::make('justification_escalation')
        //                 ->label('Justificação de Escalação')
        //                 ->wrap()
        //                 ->toggleable()
        //                 ->limit(50),        
        //             Tables\Columns\TextColumn::make('created_at')
        //                 ->label('Data de Criação')
        //                 ->dateTime()
        //                 ->toggleable()
        //                 ->sortable(),
        //             Tables\Columns\TextColumn::make('updated_at')
        //                 ->label('Data de Atualização')
        //                 ->dateTime()
        //                 ->toggleable()
        //                 ->sortable(),











        //             ] : []),
        //         // Tables\Columns\TextColumn::make('response')
        //         //     ->label('Resposta do Professor')
        //         //     ->wrap()
        //         //     ->toggleable()
        //         //     ->limit(50),
        //         Tables\Columns\TextColumn::make('status')
        //             ->label('Estado do Pedido')
        //             ->toggleable()
        //             ->badge()
        //             ->color(fn(string $state): string => match ($state) {
        //                 'Pendente' => 'warning',
        //                 'Aprovado' => 'success',
        //                 'Recusado' => 'danger',
        //                 'Escalado' => 'info',
        //                 'Aprovado DP' => 'success',
        //                 'Recusado DP' => 'danger',
        //                 default => 'gray',
        //             })
        //             ->sortable(),
        //         //
        //     ])
            // ->filters([

            //     //
            // ])
            // ->actions([
            //     // Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
