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

    public string $filtroAtual = 'meus';

    protected function getCounts(): array
    {
        $userId = Filament::auth()->id();

        $teacher = \App\Models\Teacher::where('id_user', $userId)->first();

        if (!$teacher) {
            return [
                'todos' => 0,
                'meus' => 0,
                'recebidos' => 0,
            ];
        }

        $meus = \App\Models\ScheduleRequest::where('id_teacher_requester', $teacher->id)->count();

        $recebidos = \App\Models\ScheduleRequest::whereHas('scheduleConflict', function ($q) use ($teacher) {
            $q->where('id_teacher', $teacher->id);
        })->where('status', '!=', 'Cancelado')->count();

        $todos = \App\Models\ScheduleRequest::where(function ($query) use ($teacher) {
            $query->where('id_teacher_requester', $teacher->id)
                ->orWhere(function ($sub) use ($teacher) {
                    $sub->whereHas('scheduleConflict', function ($conf) use ($teacher) {
                        $conf->where('id_teacher', $teacher->id);
                    })->where('status', '!=', 'Cancelado');
                });
        })->count();

        return [
            'todos' => $todos,
            'meus' => $meus,
            'recebidos' => $recebidos,
        ];
    }

    protected function getHeaderActions(): array
    {


        $counts = $this->getCounts();

        return [
            // Action::make('todos')
            //     ->label('Todos Pedidos (' . $counts['todos'] . ')')
            //     ->action(fn() => $this->filtroAtual = 'todos')
            //     ->color(fn() => $this->filtroAtual === 'todos' ? 'primary' : 'gray'),

            Action::make('meus')
                ->label(fn() => 'Meus Pedidos (' . $counts['meus'] . ')')
                ->action(fn() => $this->filtroAtual = 'meus')
                ->color(fn() => $this->filtroAtual === 'meus' ? 'primary' : 'gray'),

            Action::make('recebidos')
                ->label('Pedidos Recebidos (' . $counts['recebidos'] . ')')
                ->action(fn() => $this->filtroAtual = 'recebidos')
                ->color(fn() => $this->filtroAtual === 'recebidos' ? 'primary' : 'gray'),
        ];
    }


    protected function getTableQuery(): ?Builder
    {
        $userId = Filament::auth()->id();
        $teacherId = \App\Models\Teacher::where('id_user', $userId)->value('id');

        $query = parent::getTableQuery();

        return match ($this->filtroAtual) {
            // ✅ Mostra todos os pedidos feitos pelo professor (mesmo os cancelados)
            'meus' => $query
                ->where('id_teacher_requester', $teacherId),

            // ❌ Oculta pedidos cancelados para quem os recebeu
            'recebidos' => $query
                ->whereHas('scheduleConflict', function ($q) use ($teacherId) {
                    $q->where('id_teacher', $teacherId);
                })
                ->where('status', '!=', 'Cancelado'),

            // ❌ Oculta pedidos cancelados nos recebidos, mas mantém nos feitos
            default => $query
                ->where(function ($q) use ($teacherId) {
                    $q->where('id_teacher_requester', $teacherId)
                        ->orWhere(function ($sub) use ($teacherId) {
                            $sub->whereHas('scheduleConflict', function ($conf) use ($teacherId) {
                                $conf->where('id_teacher', $teacherId);
                            })->where('status', '!=', 'Cancelado');
                        });
                }),
        };
    }
}
