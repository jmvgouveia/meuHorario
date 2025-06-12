<?php

namespace App\Filament\Resources\SchedulesResource\Traits;

use App\Models\SchoolYears;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Carbon\Carbon;

trait CheckScheduleWindow
{
    protected function validateScheduleWindow(): void
    {
        $hoje = now()->toDateString();

        $anoLetivo = SchoolYears::where('active', true)->first();

        if (
            !$anoLetivo ||
            !$anoLetivo->start_date ||
            !$anoLetivo->end_date ||
            $hoje < $anoLetivo->start_date ||
            $hoje > $anoLetivo->end_date
        ) {
            Notification::make()
                ->title('Fora do período de marcação')
                ->body('Só pode marcar horários entre ' .
                    Carbon::parse($anoLetivo->start_date)->format('d/m/Y') . ' e ' .
                    Carbon::parse($anoLetivo->end_date)->format('d/m/Y') . '.')
                ->warning()
                ->persistent()
                ->send();

            throw new Halt('Fora do período permitido para marcar horários.');
        }
    }
}
