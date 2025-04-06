<?php

namespace App\Filament\Widgets;

use App\Models\Docente;
use Filament\Widgets\ChartWidget;

class DocentesOverview extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Docentes por Sexo';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $docentes = Docente::selectRaw('sexo, COUNT(*) as total')
        ->groupBy('sexo')
        ->get();

    return [
        'datasets' => [
            [
                'label' => 'Número de Docentes',
                'data' => $docentes->pluck('total')->toArray(),
                'backgroundColor' => ['#2563EB', '#DC2626'], // Azul e Vermelho (exemplo)
            ],
        ],
        'labels' => $docentes->pluck('sexo')->toArray(), // Exibe "Masculino" e "Feminino"
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
