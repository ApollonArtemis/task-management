<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Models\TaskLists;
use App\Models\Tasks;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});


Route::resource('lists', ListController::class);

Route::resource('tasks', TaskController::class);

require __DIR__.'/settings.php';
