<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Models\TaskLists;
use App\Models\Tasks;
use Inertia\Inertia;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::inertia('dashboard', 'Dashboard')->name('dashboard');
// });

Route::get('dashboard', function() {
    $lists = TaskLists::query()
    ->withCount(['tasks', 'tasks as completed_tasks_count' => function($q) {
        $q->where('cCompleted', true);
    }])
    ->latest()
    ->get();

    $recentTasks = Tasks::query()
    ->with('list:nTaskListNo,cTaskListName,cTaskListsColor')
    ->latest()
    ->take(10)
    ->get();

    $totalTasks = Tasks::count();
    $completedTasks = Tasks::where('cCompleted', true)->count();
    $pendingTasks = Tasks::where('cCompleted', false)->count();

    return Inertia::render('Dashboard', [
        'lists' => $lists,
        'recentTasks' => $recentTasks,
        'totalTasks' => $totalTasks,
        'completedTasks' => $completedTasks,
        'pendingTasks' => $pendingTasks
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


// Route::resource('lists', ListController::class);
Route::resource('lists', ListController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

Route::resource('tasks', TaskController::class)->only(['update', 'destroy', 'index']);
Route::post('/lists/tasks', [TaskController::class, 'store'])->name('tasks.store');

require __DIR__.'/settings.php';
