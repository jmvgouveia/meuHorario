<?php

namespace App\Filament\Resources\SchedulesResource\Traits;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\Schedules;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use App\Models\Student;


trait ChecksScheduleConflicts
{
    // public ?Schedules $conflictingSchedule = null;

    protected function checkScheduleConflictsAndAvailability(array $data, ?int $ignoreId = null): void
    {
        $subject = Subject::find($data['id_subject']);
        $tipo = strtolower($subject->type ?? 'letiva');

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();

        if (!$teacher) {
            Notification::make()
                ->title('Erro')
                ->body('Professor não encontrado.')
                ->danger()
                ->persistent()
                ->send();

            throw new Halt('Professor não encontrado.');
        }

        if (!empty($data['students']) && is_array($data['students'])) {
            foreach ($data['students'] as $studentId) {
                $this->checkStudentScheduleConflict($studentId, $data['id_weekday'], $data['id_timeperiod'], $ignoreId);
            }
        }

        $this->checkTeacherScheduleConflict($teacher->id, $data['id_weekday'], $data['id_timeperiod'], $ignoreId);


        if ($subject && !in_array(strtolower((string) $subject->subject), ['reunião', 'tee'])) {
            $this->checkRoomScheduleConflict(
                $data['id_room'],
                $data['id_weekday'],
                $data['id_timeperiod'],
                $ignoreId
            );
        }

        $this->checkWorkload($teacher->id, $tipo);
    }

    private function checkTeacherScheduleConflict(int $idTeacher, int $weekday, int $timeperiod, ?int $ignoreId = null): void
    {
        $query = Schedules::where('id_teacher', $idTeacher)
            ->where('id_weekday', $weekday)
            ->where('id_timeperiod', $timeperiod)
            ->where('status', '!=', 'Recusado DP') // Exclude pending schedules
            ->where('status', '!=', 'Eliminado')
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId));

        // if ($ignoreId) {
        //     $query->where('id', '!=', $ignoreId);
        // }

        if ($query->exists()) {
            Notification::make()
                ->title('Conflito de horário detetado')
                ->body("Já tem uma atividade marcada neste horário.")
                ->warning()
                ->persistent()
                ->send();

            throw new Halt('Erro: O professor já tem uma atividade neste horário.');
        }
    }
    //---
    public function checkStudentScheduleConflict(string|int $studentNumber, int $weekday, int $timeperiod, ?int $ignoreId = null): void
    {
        // 1. Obter ID do aluno a partir do número
        $student = Student::where('id', $studentNumber)->first();

        if (!$student) {
            Notification::make()
                ->title('Aluno não encontrado')
                ->body("Não foi encontrado aluno com o número {$studentNumber}.")
                ->danger()
                ->send();

            throw new Halt("Erro: O Aluno {$student->name} já tem uma atividade neste horário.");
            // impede erro visual no Filament
        }

        // 2. Verificar se esse aluno tem marcação nesse dia/período
        $conflict = Schedules::whereHas(
            'students',
            fn($q) =>
            $q->where('id_student', $student->id)
        )
            ->where('id_weekday', $weekday)
            ->where('id_timeperiod', $timeperiod)
            ->whereNotIn('status', ['Recusado DP', 'Eliminado'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        // 3. Se existir conflito, notificar e parar
        if ($conflict) {
            Notification::make()
                ->title('Conflito de horário detetado')
                ->body("O aluno {$student->name} já tem uma atividade neste horário.")
                ->warning()
                ->persistent()
                ->send();

            throw new Halt("Erro: O Aluno {$student->name} já tem uma atividade neste horário.");
        }
    }
    //---


    private function checkRoomScheduleConflict(int $idRoom, int $weekday, int $timeperiod, ?int $ignoreId = null): void
    {


        $query = Schedules::where('id_room', $idRoom)
            ->where('id_weekday', $weekday)
            ->where('id_timeperiod', $timeperiod)
            ->whereNotIn('status', ['Recusado DP', 'Eliminado'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId));


        $this->conflictingSchedule = $query->with('teacher')->first();

        if ($this->conflictingSchedule) {
            $prof = $this->conflictingSchedule->teacher->name ?? 'outro professor';

            Notification::make()
                ->title("Conflito de horário detetado")
                ->body("Já existe um agendamento com o(a) professor(a) {$prof} nesta sala e horário. Altere o horário, a sala, ou solicite uma troca.")
                ->warning()
                ->persistent()
                ->send();

            throw new Halt('Erro: Conflito de horário na sala.');
        }
    }

    private function checkWorkload(int $idTeacher, string $tipo): void
    {
        $counter = TeacherHourCounter::where('id_teacher', $idTeacher)->first();

        if (!$counter) {
            Notification::make()
                ->title('Contador de horas não encontrado')
                ->body("Contador de horas não encontrado para o professor.")
                ->warning()
                ->persistent()
                ->send();

            throw new Halt('Contador de horas não encontrado para o professor.');
        }

        if ($tipo === 'nao letiva') {
            if ($counter->carga_componente_naoletiva <= 0) {
                Notification::make()
                    ->title('Sem horas disponíveis')
                    ->body('Sem horas disponíveis na componente NÃO LETIVA.')
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Sem horas disponíveis na componente não letiva.');
            }
        } else {
            if ($counter->carga_componente_letiva <= 0) {
                Notification::make()
                    ->title('Sem horas disponíveis')
                    ->body('Sem horas disponíveis na componente LETIVA.')
                    ->warning()
                    ->persistent()
                    ->send();

                throw new Halt('Sem horas disponíveis na componente letiva.');
            }
        }
    }
}
