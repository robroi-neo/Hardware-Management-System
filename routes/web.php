<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Pos\CheckoutController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ProductController;
use App\Http\Controllers\Pos\RefundController;
use App\Http\Controllers\Pos\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Purchasing\InvoiceRefundController;
use App\Http\Controllers\Purchasing\InvoiceDetailController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.select-branch');
});


# Routes for logged in users
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/pos', function () {
        return view('modules.pos.new-sale');
    })->middleware('permission:pos.access')->name('pos');

    Route::get('/pos/transactions', [TransactionController::class, 'index'])
        ->middleware('permission:sales.view-history')->name('pos.transactions');

    Route::post('/pos/transactions/{sale}/refund', [RefundController::class, 'store'])
        ->middleware('permission:sales.refund')->name('pos.transactions.refund');

    Route::get('/purchasing/new-invoice', function () {
        // Get selected branch from session (set in branch selection before login)
        $branch = session('branch');
        if (!$branch || !isset($branch['id'])) {
            return redirect('/')->with('error', 'No branch selected. Please select a branch first.');
        }

        $suppliers = \App\Models\Supplier::where('status', '=', 'active')->get();
        $branches = \App\Models\Branch::all();
        $branchId = $branch['id'];
        $terminalName = $branch['name'] ?? 'Selected Branch';
        $selectedBranch = $branches->find($branchId);
        $selectedSupplierId = session('purchasing_supplier_id');

        return view('modules.purchasing.new-invoice', compact('suppliers', 'branches', 'branchId', 'terminalName', 'selectedBranch', 'selectedSupplierId'));
    })->middleware('permission:purchases.create')->name('purchasing.new-invoice');

    Route::get('/purchasing/invoice-history', [\App\Http\Controllers\Purchasing\InvoicesController::class, 'index'])
        ->middleware('permission:purchases.view-history')->name('purchasing.invoice-history');

    Route::get('purchasing/invoices/{invoice}', [InvoiceDetailController::class, 'show'])->name('purchasing.invoices.show');

    Route::post('/purchasing/invoices/{invoice}/refund', [InvoiceRefundController::class, 'store'])
        ->middleware('permission:purchases.refund')
        ->name('purchasing.invoices.refund');

    Route::get('/inventory/overview', [\App\Http\Controllers\Inventory\OverviewController::class, 'index'])
        ->middleware('permission:inventory.view-overview')->name('inventory.overview');

    // Archive a product (mark as archived)
    Route::patch('/inventory/products/{product}/archive', [\App\Http\Controllers\Inventory\OverviewController::class, 'archive'])
        ->middleware('permission:inventory.update')
        ->name('inventory.products.archive');

    // Restore a product (un-archive)
    Route::patch('/inventory/products/{product}/restore', [\App\Http\Controllers\Inventory\OverviewController::class, 'restore'])
        ->middleware('permission:inventory.update')
        ->name('inventory.products.restore');

    Route::get('/inventory/manual-stock-in', [\App\Http\Controllers\Inventory\StockInController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.manual-stock-in');

    Route::get('/inventory/stock-out', [\App\Http\Controllers\Inventory\StockOutController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.stock-out');

    Route::get('/inventory/stock-movements', [\App\Http\Controllers\Inventory\StockMovementController::class, 'index'])
        ->middleware('permission:inventory.view-movements')->name('inventory.stock-movements');

    Route::get('/audit-logs/user-activity', [\App\Http\Controllers\Audit\UserActivityController::class, 'index'])
        ->middleware('permission:audit.user-activity.view')
        ->name('audit-logs.user-activity');

    Route::get('/audit-logs/system-logs', function () {
        return view('modules.audit-logs.system-logs');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.system-logs');

    Route::get('/audit-logs/archives', function () {
        return view('modules.audit-logs.archives');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.archives');

    // Supplier routes
    Route::resource('suppliers', \App\Http\Controllers\Supplier\SupplierController::class)
        ->middleware('permission:suppliers.view');

    Route::patch('/suppliers/{supplier}/deactivate', [\App\Http\Controllers\Supplier\SupplierController::class, 'deactivate'])
        ->middleware('permission:suppliers.delete')
        ->name('suppliers.deactivate');

    Route::patch('/suppliers/{supplier}/activate', [\App\Http\Controllers\Supplier\SupplierController::class, 'activate'])
        ->middleware('permission:suppliers.delete')
        ->name('suppliers.activate');

    Route::get('/suppliers/api/search', [\App\Http\Controllers\Supplier\SupplierController::class, 'search'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.api.search');

    // User routes this shit is the tables.
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view-list')
        ->name('users.index');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    // Create User this shit is the create user.
    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    // Create user routes API
    Route::post('/users/create',[UserController::class,'store'])
        ->name('users.store');

    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('permission:users.delete')
        ->name('users.deactivate');

    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])
        ->middleware('permission:users.delete')
        ->name('users.activate');

    Route::get('/users/api/search', [UserController::class, 'search'])
        ->middleware('permission:users.view-list')
            ->name('users.api.search');

    // POS API endpoints
    Route::prefix('pos/api')->group(function () {
        Route::get('products/search', [ProductController::class, 'search'])->name('pos.api.products.search');
        Route::get('products/browse', [ProductController::class, 'browse'])->name('pos.api.products.browse');

        Route::get('cart', [PosController::class, 'getCart'])->name('pos.api.cart.get');
        Route::post('cart/add', [PosController::class, 'addItem'])->name('pos.api.cart.add');
        Route::post('cart/update', [PosController::class, 'updateItem'])->name('pos.api.cart.update');
        Route::post('cart/remove', [PosController::class, 'removeItem'])->name('pos.api.cart.remove');
        Route::post('cart/markup', [PosController::class, 'markup'])->name('pos.api.cart.markup');

        Route::get('checkout/prepare', [CheckoutController::class, 'prepare'])->name('pos.api.checkout.prepare');
        Route::post('checkout/finalize', [CheckoutController::class, 'finalize'])->name('pos.api.checkout.finalize');
    });

// Moved out — resolves to /pos/transactions/{sale}
    Route::get('pos/transactions/{sale}', [TransactionController::class, 'show'])->name('pos.transactions.show');

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

        Route::get('checkout/current', [\App\Http\Controllers\Purchasing\CheckoutController::class, 'current'])->name('purchasing.api.checkout.current');
        Route::get('checkout/prepare', [\App\Http\Controllers\Purchasing\CheckoutController::class, 'prepare'])->name('purchasing.api.checkout.prepare');
        Route::post('checkout/finalize', [\App\Http\Controllers\Purchasing\CheckoutController::class, 'finalize'])->name('purchasing.api.checkout.finalize');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
