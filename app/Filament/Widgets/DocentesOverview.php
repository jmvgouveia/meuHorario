<?php

namespace App\Filament\Widgets;

use App\Models\Docente;
use App\Models\Teacher;
use App\Models\Gender;
use Filament\Widgets\ChartWidget;

class DocentesOverview extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Docentes por Sexo';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
       // Buscar a distribuição de docentes por gênero
       $docentes = Teacher::selectRaw('genders.gender, COUNT(*) as total')
       ->join('genders', 'teachers.id_gender', '=', 'genders.id')  // Junção com a tabela 'genders'
       ->groupBy('genders.gender')  // Agrupar por gênero
       ->get();

   return [
       'datasets' => [
           [
               'label' => 'Número de Docentes',
               'data' => $docentes->pluck('total')->toArray(),
               'backgroundColor' => ['#2563EB', '#DC2626'], // Azul e Vermelho (exemplo)
           ],
       ],
       'labels' => $docentes->pluck('gender')->toArray(), // Labels com os nomes dos gêneros
   ];

    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
