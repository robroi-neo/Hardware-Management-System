<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\BranchSelectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('select-branch', [BranchSelectionController::class, 'create'])
        ->name('branch.select');

    Route::post('select-branch', [BranchSelectionController::class, 'store'])
        ->name('branch.store');

    Route::delete('select-branch', [BranchSelectionController::class, 'destroy'])
        ->name('branch.destroy');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
