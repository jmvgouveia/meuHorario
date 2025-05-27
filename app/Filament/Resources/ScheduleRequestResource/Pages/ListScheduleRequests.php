<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Facades\Filament;  // <-- Importar aqui
use Illuminate\Database\Eloquent\Builder;
use App\Models\ScheduleRequest;
use App\Models\Teacher;


class ListScheduleRequests extends ListRecords
{
    protected static string $resource = ScheduleRequestResource::class;

    public string $filtroAtual = 'todos';

protected function getCounts(): array
{
    $userId = Filament::auth()->id();

    $teacher = Teacher::where('id_user', $userId)->first();

    if (!$teacher) {
        return [
            'todos' => 0,
            'meus' => 0,
            'recebidos' => 0,
        ];
    }

    $todos = ScheduleRequest::where(function ($query) use ($teacher) {
        $query->where('id_teacher_requester', $teacher->id)
            ->orWhereHas('scheduleConflict', function ($q) use ($teacher) {
                $q->where('id_teacher', $teacher->id);
            });
    })->count();

    $meus = ScheduleRequest::where('id_teacher_requester', $teacher->id)->count();

    $recebidos = ScheduleRequest::whereHas('scheduleConflict', function ($q) use ($teacher) {
        $q->where('id_teacher', $teacher->id);
    })->count();

    return [
        'todos' => $todos,
        'meus' => $meus,
        'recebidos' => $recebidos,
    ];
}

    protected function getHeaderActions(): array
        {

            // $countTodos = 10;      // total pedidos (exemplo)
            // $countMeus = 3;        // meus pedidos
            // $countRecebidos = 5;   // pedidos recebidos

    $counts = $this->getCounts();

        return [
            Action::make('todos')
                ->label('Todos Pedidos (' . $counts['todos'] . ')')
                ->action(fn () => $this->filtroAtual = 'todos')
                ->color(fn () => $this->filtroAtual === 'todos' ? 'primary' : 'secondary'),

            Action::make('meus')
                ->label(fn () => 'Meus Pedidos (' . $counts['meus'] . ')')
                ->action(fn () => $this->filtroAtual = 'meus')
                ->color(fn () => $this->filtroAtual === 'meus' ? 'primary' : 'secondary'),
               

            Action::make('recebidos')
                ->label('Pedidos Recebidos (' . $counts['recebidos'] . ')')
                ->action(fn () => $this->filtroAtual = 'recebidos')
                ->color(fn () => $this->filtroAtual === 'recebidos' ? 'primary' : 'secondary'),
        ];
    }

    
    protected function getTableQuery(): ?Builder
    {
        $userId = Filament::auth()->id();
        $teacherId = \App\Models\Teacher::where('id_user', $userId)->value('id');

        $query = parent::getTableQuery();

        return match ($this->filtroAtual) {
            'meus' => $query->where('id_teacher_requester', $teacherId),
            'recebidos' => $query->whereHas('scheduleConflict', function ($q) use ($teacherId) {
                $q->where('id_teacher', $teacherId);
            }),
            default => $query->where(function ($q) use ($teacherId) {
                $q->where('id_teacher_requester', $teacherId)
                    ->orWhereHas('scheduleConflict', function ($sub) use ($teacherId) {
                        $sub->where('id_teacher', $teacherId);
                    });
            }),
        };
    }
}
