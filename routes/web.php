<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('modules.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

# Routes for logged in users
Route::middleware('auth')->group(function () {
    Route::get('/pos', function () {
        return view('modules.pos.new-sale');
    })->middleware('permission:pos.access')->name('pos');

    Route::get('/pos/transactions', function () {
        return view('modules.pos.transactions');
    })->middleware('permission:sales.view-history')->name('pos.transactions');

    Route::get('/purchasing/new-invoice', function () {
        return view('modules.purchasing.new-invoice');
    })->middleware('permission:purchases.create')->name('purchasing.new-invoice');

    Route::get('/purchasing/invoice-history', function () {
        return view('modules.purchasing.invoice-history');
    })->middleware('permission:purchases.view-history')->name('purchasing.invoice-history');

    Route::get('/inventory/overview', function () {
        return view('modules.inventory.stock-overview');
    })->middleware('permission:inventory.view-overview')->name('inventory.overview');

    Route::get('/inventory/manual-stock-in', function () {
        return view('modules.inventory.manual-stock-in');
    })->middleware('permission:inventory.update')->name('inventory.manual-stock-in');

    Route::get('/inventory/stock-out', function () {
        return view('modules.inventory.stock-out');
    })->middleware('permission:inventory.update')->name('inventory.stock-out');

    Route::get('/inventory/stock-movements', function () {
        return view('modules.inventory.stock-movements');
    })->middleware('permission:inventory.view-movements')->name('inventory.stock-movements');

    Route::get('/inventory/archives', function () {
        return view('modules.inventory.archives');
    })->middleware('permission:inventory.archive')->name('inventory.archives');

    Route::get('/audit-logs/user-activity', function () {
        return view('modules.audit-logs.user-activity');
    })->middleware('permission:audit.user-activity.view')->name('audit-logs.user-activity');

    Route::get('/audit-logs/system-logs', function () {
        return view('modules.audit-logs.system-logs');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.system-logs');

    Route::get('/audit-logs/archives', function () {
        return view('modules.audit-logs.archives');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.archives');

    Route::get('/suppliers', function () {
        return view('modules.suppliers.index');
    })->middleware('permission:suppliers.view')->name('suppliers.index');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
