<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');
    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');

    Route::get('/boards/{board}', [BoardController::class, 'show'])->name('boards.show');
    Route::post('/boards/{board}/columns', [ColumnController::class, 'store'])->name('columns.store');

    Route::post('/boards/{board}/columns/{column}/tasks', [TaskController::class, 'store'])->name('tasks.store');

    Route::post('/boards/{board}/columns/{column}/tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');

    Route::patch('/boards/{board}/columns/{column}/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
