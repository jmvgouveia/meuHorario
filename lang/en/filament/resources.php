<?php

return [
    'rooms' => [
        'single' => 'Room',
        'plural' => 'Rooms',
        'navigation_label' => 'Rooms',
        'fields' => [
            'name' => 'Name',
            'building' => 'Building',
            'capacity' => 'Capacity',
        ],
    ],
    'buildings' => [
        'single' => 'Building',
        'plural' => 'Buildings',
        'navigation_label' => 'Buildings',
        'fields' => [
            'name' => 'Name',
            'address' => 'Address',
            'rooms_count' => 'Rooms',
        ],
    ],
    'time_periods' => [
        'single' => 'Time Period',
        'plural' => 'Time Periods',
        'navigation_label' => 'Time Periods',
        'fields' => [
            'description' => 'Description',
        ],
    ],
]; 