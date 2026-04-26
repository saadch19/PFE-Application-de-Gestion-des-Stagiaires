<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\InternshipRequestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:Administrateur')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('role:Administrateur,Responsable de competence')->group(function (): void {
        Route::resource('interns', InternController::class)->except(['show']);
        Route::patch('/interns/{intern}/archive', [InternController::class, 'archive'])->name('interns.archive');
        Route::patch('/interns/{intern}/restore', [InternController::class, 'restore'])->name('interns.restore');

        Route::resource('internships', InternshipController::class)->except(['show']);
        Route::patch('/internships/{internship}/status', [InternshipController::class, 'updateStatus'])->name('internships.status');

        Route::resource('absences', AbsenceController::class)->except(['show']);
    });

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    Route::middleware('role:Administrateur,Responsable de competence,Encadrant')->group(function (): void {
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    Route::get('/requests', [InternshipRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [InternshipRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [InternshipRequestController::class, 'store'])->name('requests.store');
    Route::patch('/requests/{requestItem}/process', [InternshipRequestController::class, 'process'])->name('requests.process');
    Route::delete('/requests/{requestItem}', [InternshipRequestController::class, 'destroy'])->name('requests.destroy');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');

    Route::get('/attestations/{intern}', [AttestationController::class, 'show'])->name('attestations.show');
});
