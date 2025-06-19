<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;




Route::get('/', function () {
    return view('welcome');
});


view('components.timeline.schedule-request')->render();

Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
