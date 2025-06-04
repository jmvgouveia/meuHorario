<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-blade', function () {
    return view('components.timeline.schedule-request');
});


view('components.timeline.schedule-request')->render();
