<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserController;
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

    Route::get('/pos/transactions', [\App\Http\Controllers\Pos\TransactionController::class, 'index'])
        ->middleware('permission:sales.view-history')->name('pos.transactions');


    Route::get('/purchasing/new-invoice', function () {
        // Get terminal from session
        $posTerminal = session('pos_terminal');
        if (!$posTerminal || !isset($posTerminal['branch_id'])) {
            return redirect('/')->with('error', 'No terminal selected. Please select a terminal first.');
        }

        $suppliers = \App\Models\Supplier::where('status', 'active')->get();
        $branches = \App\Models\Branch::all();
        $branchId = $posTerminal['branch_id'];
        $terminalName = $posTerminal['terminal_name'] ?? 'Unknown Terminal';
        $selectedBranch = $branches->find($branchId);
        $selectedSupplierId = session('purchasing_supplier_id');

        return view('modules.purchasing.new-invoice', compact('suppliers', 'branches', 'branchId', 'terminalName', 'selectedBranch', 'selectedSupplierId'));
    })->middleware('permission:purchases.create')->name('purchasing.new-invoice');

    Route::get('/purchasing/invoice-history', [\App\Http\Controllers\Purchasing\InvoiceHistoryController::class, 'index'])
        ->middleware('permission:purchases.view-history')->name('purchasing.invoice-history');

    Route::get('/inventory/overview', [\App\Http\Controllers\Inventory\OverviewController::class, 'index'])
        ->middleware('permission:inventory.view-overview')->name('inventory.overview');

    Route::get('/inventory/manual-stock-in', [\App\Http\Controllers\Inventory\StockInController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.manual-stock-in');

    Route::get('/inventory/stock-out', [\App\Http\Controllers\Inventory\StockOutController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.stock-out');

    Route::get('/inventory/stock-movements', [\App\Http\Controllers\Inventory\StockMovementController::class, 'index'])
        ->middleware('permission:inventory.view-movements')->name('inventory.stock-movements');

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

    // Supplier routes
    Route::resource('suppliers', \App\Http\Controllers\Supplier\SupplierController::class)
        ->middleware('permission:suppliers.view');

    // User routes
    Route::get('/users', function () {
        return view('modules.users.users');
    })->middleware('permission:users.view-list')->name('users.index');


    // Create User
    Route::get('/users/create', function () {
        return view('modules.users.new-user');
    })->middleware('permission:users.create')->name('users.create');
    // Create user routes API
    Route::post('/users/create',[UserController::class,'store'])
        ->name('users.store');

    // POS API endpoints
    Route::prefix('pos/api')->group(function () {
        Route::get('products/search', [\App\Http\Controllers\Pos\ProductController::class, 'search'])->name('pos.api.products.search');
        Route::get('products/browse', [\App\Http\Controllers\Pos\ProductController::class, 'browse'])->name('pos.api.products.browse');

        Route::get('cart', [\App\Http\Controllers\Pos\PosController::class, 'getCart'])->name('pos.api.cart.get');
        Route::post('cart/add', [\App\Http\Controllers\Pos\PosController::class, 'addItem'])->name('pos.api.cart.add');
        Route::post('cart/update', [\App\Http\Controllers\Pos\PosController::class, 'updateItem'])->name('pos.api.cart.update');
        Route::post('cart/remove', [\App\Http\Controllers\Pos\PosController::class, 'removeItem'])->name('pos.api.cart.remove');

        Route::get('checkout/prepare', [\App\Http\Controllers\Pos\CheckoutController::class, 'prepare'])->name('pos.api.checkout.prepare');
        Route::post('checkout/finalize', [\App\Http\Controllers\Pos\CheckoutController::class, 'finalize'])->name('pos.api.checkout.finalize');
    });

    // Inventory API endpoints
    Route::prefix('inventory/api')->group(function () {
        Route::get('products/search', [\App\Http\Controllers\Inventory\StockInController::class, 'searchProducts'])->name('inventory.api.products.search');
        Route::post('stock-in/store', [\App\Http\Controllers\Inventory\StockInController::class, 'store'])->name('inventory.api.stock-in.store');
        Route::post('stock-out/store', [\App\Http\Controllers\Inventory\StockOutController::class, 'store'])->name('inventory.api.stock-out.store');
    });

    // Purchasing API endpoints
    Route::prefix('purchasing/api')->middleware('permission:purchases.create')->group(function () {
        Route::get('products/search', [\App\Http\Controllers\Purchasing\ProductController::class, 'search'])->name('purchasing.api.products.search');
        Route::get('products/preview', [\App\Http\Controllers\Purchasing\ProductController::class, 'preview'])->name('purchasing.api.products.preview');
        Route::post('products/store', [\App\Http\Controllers\Purchasing\ProductController::class, 'store'])->name('purchasing.api.products.store');

        Route::get('cart', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'getCart'])->name('purchasing.api.cart.get');
        Route::post('cart/add', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'addItem'])->name('purchasing.api.cart.add');
        Route::post('cart/update', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'updateItem'])->name('purchasing.api.cart.update');
        Route::post('cart/remove', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'removeItem'])->name('purchasing.api.cart.remove');
        Route::post('cart/clear', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'clearCart'])->name('purchasing.api.cart.clear');

        Route::get('supplier', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'getSelectedSupplier'])->name('purchasing.api.supplier.get');
        Route::post('supplier', [\App\Http\Controllers\Purchasing\PurchasingController::class, 'setSelectedSupplier'])->name('purchasing.api.supplier.set');

        Route::get('checkout/prepare', [\App\Http\Controllers\Purchasing\CheckoutController::class, 'prepare'])->name('purchasing.api.checkout.prepare');
        Route::post('checkout/finalize', [\App\Http\Controllers\Purchasing\CheckoutController::class, 'finalize'])->name('purchasing.api.checkout.finalize');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
