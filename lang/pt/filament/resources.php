<?php

return [
    'rooms' => [
        'single' => 'Sala',
        'plural' => 'Salas',
        'navigation_label' => 'Salas',
        'fields' => [
            'name' => 'Nome',
            'building' => 'Edifício',
            'capacity' => 'Capacidade',
        ],
    ],
    'buildings' => [
        'single' => 'Edifício',
        'plural' => 'Edifícios',
        'navigation_label' => 'Edifícios',
        'fields' => [
            'name' => 'Nome',
            'address' => 'Morada',
            'rooms_count' => 'Salas',
        ],
    ],
    'time_periods' => [
        'single' => 'Período',
        'plural' => 'Períodos',
        'navigation_label' => 'Períodos',
        'fields' => [
            'description' => 'Descrição',
        ],
    ],
    // 'departments' => [
    //     'single' => 'Departamento',
    //     'plural' => 'Departamentos',
    //     'navigation_label' => 'Departamentos',
    //     'fields' => [
    //         'department' => 'Departamento',
    //         'department_description' => 'Descrição do Departamento',
    //     ],
    // ],

];
