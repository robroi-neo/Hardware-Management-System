# AGENTS.md - RNM Hardware Management System

Guidance for AI agents working on this Laravel hardware management & POS system.

## Project Overview

This is a **multi-module business management system** built with Laravel 13 (latest), featuring:
- **Point of Sale (POS)** system with session-based cart management
- **Inventory Management** with branch-specific stock tracking
- **Purchasing & Invoicing** for supplier orders
- **Supplier Management** for vendor records
- **Audit Logs** for user activity and system events
- **Role-Based Access Control (RBAC)** using Spatie Permission package

**Database**: SQLite (single file: `database/database.sqlite`)  
**Frontend**: Blade templates + Tailwind CSS + Alpine.js  
**Test Framework**: Pest.php (v4)

---

## Architecture & Critical Data Flows

### Core Entity Relationships

```
Branch (store locations)
  ├─ BranchInventory (per-branch stock levels)
  │   ├─ Product (hardware catalog)
  │   └─ Sales (point-of-sale transactions)
  │        └─ SaleItem (individual line items)
  └─ PosTerminal (POS device assignment)

Product (shared catalog)
  ├─ BranchInventory (qty per branch)
  └─ SaleItem (sales references)

User
  ├─ Sales (cashier who made transaction)
  └─ Roles (via Spatie\Permission)

Invoice
  └─ Purchase (supplier orders)

Supplier (vendors)
  └─ Purchase (supplier orders)
```

### POS System (Session-Based, Not DB-Persistent)

**Terminal Selection**: Session key `pos_terminal` (dict with `id`, `terminal_id`, `terminal_name`, `branch_id`, `branch_name`); `pos_terminal.branch_id` used for branch scoping.

**Cart Storage**: Session key `pos_cart` (array of `{product_id, quantity}`)

**Data Flow**:
1. **Terminal Selection** → User selects POS terminal (from `PosTerminal` model); terminal determines `branch_id` for all subsequent operations
2. **Product Search/Browse** (`Pos\ProductController::{search,browse}`) → Search active products, attach `available_quantity` from `BranchInventory` (branch-scoped to terminal's branch)
3. **Cart Mutations** (`Pos\PosController`) → Add/update/remove items in session (no DB write until checkout)
4. **Checkout Prepare** (`Pos\CheckoutController::prepare`) → Hydrate cart with unit price and totals (preview)
5. **Checkout Finalize** (`Pos\CheckoutController::finalize`) → **Atomic transaction**:
   - Resolve branch ID from terminal (via `resolveTerminalBranchId()`)
   - Lock branch inventory rows (`lockForUpdate()`)
   - Validate stock availability
   - Create `Sale` record with `payment_method` (defaults to 'cash')
   - Create `SaleItem` records (one per product: `product_id`, `quantity`, `markup`, `subtotal`)
   - Decrement `BranchInventory.quantity`
   - Clear session cart

**Key Detail**: Pricing uses `Product.capital` (cost) for sales price (no separate markup column on Product). `SaleItem` does not store `product_name`, `unit`, `unit_price`, or `cost`—these are calculated dynamically in checkout prepare/finalize.

### Purchasing System (Session-Based Cart → Atomic Invoice/Purchase Creation)

**Cart Storage**: Session key `purchasing_cart` (array of `{product_id, quantity, unit_price}`)

**Product Standardization**: All product names are standardized to `UPPERCASE_WITH_UNDERSCORES` format during creation. Case-insensitive uniqueness validation prevents duplicates (e.g., "Hammer" vs "hammer" treated as same). See `PRODUCT_STANDARDIZATION.md` for detailed rules.

**Hybrid Product Creation**: Users can create products on-the-fly during checkout via modal, providing:
- **Product Name** (standardized automatically, case-insensitive uniqueness check)
- **Unit** (e.g., 'pcs', 'box', 'meter', default 'pcs')
- **Capital** (cost price, stored as `Product.capital`)

**Data Flow**:
1. **Supplier & Branch Selection** → User selects supplier (active only) and branch for the purchase
2. **Product Search** (`Purchasing\ProductController::search`) → Search existing products by name/ID, return `{id, name, unit, capital}`
3. **Inline Product Creation** (optional) → Click "New Product" modal to create new product with standardization & uniqueness check; added to cart immediately upon creation
4. **Cart Mutations** (`Purchasing\PurchasingController`) → Add/update/remove items from `purchasing_cart` session (no DB write until checkout)
5. **Checkout Prepare** (`Purchasing\CheckoutController::prepare`) → Hydrate cart with product details and calculate subtotals (preview)
6. **Checkout Finalize** (`Purchasing\CheckoutController::finalize`) → **Atomic transaction**:
   - Create `Purchase` record (supplier_id, branch_id, date)
   - Create `PurchaseDetail` records (one per cart item: product_id, quantity, unit_price, subtotal)
   - For each product: increment `BranchInventory.quantity` (create entry if missing)
   - Create single `Invoice` record linked to Purchase (date_issued=now, date_due=now+30 days, configurable offset)
   - Clear session cart

**Key Details**:
- **Invoice Due Date**: Defaults to net-30 (30 days from today), overridable via `date_due_offset` parameter in checkout finalize
- **Inventory Increment**: Purchases increment `BranchInventory` for the selected branch (unlike POS which decrements)
- **Product Pricing**: `PurchaseDetail.unit_price` can override `Product.capital` per line item (allows wholesale price negotiation per supplier)

### Permission-Based Access Control

Routes use Spatie Permission middleware: `->middleware('permission:MODULE.ACTION')`

**Example Permissions**:
- `pos.access` → View POS page
- `sales.view-history` → View past transactions
- `inventory.update` → Perform stock-in/out
- `purchases.create` → Create new invoices
- `audit.user-activity.view` → View user logs

Permissions are seeded in `database/seeders/DatabaseSeeder.php` when running seeders (for example `php artisan migrate --seed`).

---

## Project-Specific Conventions

### Naming & Structure

| Aspect | Convention | Example |
|--------|-----------|---------|
| **Routes** | Slugified module names | `/pos`, `/purchasing`, `/inventory`, `/audit-logs` |
| **View Folders** | `resources/views/modules/{module_name}/**` | `modules/pos/new-sale.blade.php` |
| **API Routes** | Prefix `{module}/api` under auth middleware | `pos/api/cart/add`, `pos/api/products/search` |
| **Controllers** | Namespace `App\Http\Controllers\{Module}` | `Pos\ProductController`, `Pos\CheckoutController` |
| **Models** | Mixed fillable styles: PHP attributes and `$fillable` arrays | `User` uses `#[Fillable(...)]`; `Product` uses `protected $fillable = [...]` |

### Blade Component Patterns

**Sidebar System** (`resources/views/components/sidebar/`):
- `<x-sidebar.container>` — Wraps entire sidebar (handles mobile/desktop toggle via Alpine)
- `<x-sidebar.dropdown>` — Collapsible menu group (check with `@canany(['perm1', 'perm2'])`)
- `<x-sidebar.item>` — Individual menu link (use `request()->routeIs('pattern*')` for active state)

**Table Components** (`resources/views/components/table/`):
- `<x-table.sortable-header>` — Reusable sortable column header with toggle arrows. Props: `label`, `sortBy`, `sortDir`, `column`, `route`, `params` (array), `align` ('left'|'center'|'right'). **Example usage**:
  ```blade
  <x-table.sortable-header 
      label="Product Name"
      :sortBy="$sortBy"
      :sortDir="$sortDir"
      column="name"
      route="inventory.overview"
      :params="['search' => $search]"
  />
  ```
- `<x-table.pagination>` — Reusable pagination UI with results count and page links. Props: `paginator` (Paginator instance). Shows "Showing X to Y of Z results" with prev/next/page-number links.
- `<x-table.empty-state>` — Reusable empty table message. Props: `colspan` (int), `message` (string).

**Filter Components** (`resources/views/components/filters/`):
- `<x-filters.dropdown-filter>` — **Generic** reusable filter dropdown for any model/field combination. Props: `items` (Collection), `selected` (value), `route`, `params` (array), `label`, `filterName` (query param name), `valueField` (model field for option value, default 'id'), `displayField` (model field for display text, default 'name'), `minCount` (show dropdown if items >= minCount, default 2), `class` (extra CSS). Auto-submits on change. **Examples**:
  ```blade
  {{-- Branch filter --}}
  <x-filters.dropdown-filter
      :items="$branches"
      :selected="$filterBranchId"
      route="inventory.overview"
      :params="['search' => $search]"
      label="Filter by Branch"
      filterName="branch_id"
  />

  {{-- Supplier filter --}}
  <x-filters.dropdown-filter
      :items="$suppliers"
      :selected="$filterSupplierId"
      route="suppliers.index"
      label="Filter by Supplier"
      filterName="supplier_id"
      displayField="supplier_name"
  />

  {{-- Status filter (show even with 1 item) --}}
  <x-filters.dropdown-filter
      :items="$statuses"
      :selected="$filterStatus"
      route="purchases.index"
      label="Filter by Status"
      filterName="status"
      valueField="value"
      displayField="label"
      :minCount="1"
  />
  ```

- `<x-filters.branch-select>` — **Branch-specific** wrapper around `dropdown-filter` (convenience component). Props: `branches` (Collection), `selected` (branch_id), `route`, `params` (array to preserve during filter), `label`. **Example**:
  ```blade
  <x-filters.branch-select 
      :branches="$allBranches"
      :selected="$filterBranchId"
      route="inventory.overview"
      :params="['search' => $search, 'sort_by' => $sortBy]"
      label="Filter by Branch"
  />
  ```

**Layout**:
- `app.blade.php` — Main authenticated layout with sidebar, header, content area
- Uses Alpine.js state: `mobileOpen` (mobile menu toggle) and `sidebarOpen` (desktop sidebar width)
- CSRF token and Vite asset loading in head

### Model Conventions

**Casting & Relationships**:
```php
#[Fillable(['name', 'price'])]
protected $casts = ['price' => 'float', 'date' => 'datetime'];
public function items() { return $this->hasMany(Item::class); }
```

**Query Scopes**:
```php
public function scopeSearch(Builder $query, ?string $term) {
    // Case-insensitive LIKE search, handles null gracefully
}
```

**Table Name Override**:
```php
protected $table = 'branch_inventory'; // Explicit if not Model pluralized
```

### Testing & Development Commands

| Command | Purpose |
|---------|---------|
| `php artisan migrate` | Run all pending migrations (SQLite) |
| `php artisan migrate:fresh --seed` | Reset DB + re-run seeders (use carefully!) |
| `php artisan tinker` | Interactive shell for testing models/queries |
| `./vendor/bin/pest` | Run Pest tests (Unit + Feature) |
| `composer test` | Clear config and run Laravel test suite (`php artisan test`) |
| `./vendor/bin/pint` | Format code (PSR-12 Laravel style, 4-space indent) |
| `composer run dev` | Start app stack with `serve`, `queue:listen`, `pail`, and Vite via `concurrently` |
| `composer run setup` | Bootstrap app (`install`, `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`) |
| `npm run dev` | Start Vite dev server (HMR for CSS/JS) |
| `npm run build` | Compile assets for production |

---

## Integration Points & External Dependencies

### Key Packages

| Package | Purpose | Note |
|---------|---------|------|
| `spatie/laravel-permission` | RBAC roles/permissions | Config: `config/permission.php`, migrations auto-created |
| `tailwindcss`, `@tailwindcss/forms` | UI styling | Configured in `tailwind.config.js` |
| `alpinejs` | Frontend interactivity | Loaded via Vite in layouts |
| `laravel-vite-plugin` | Asset bundling | Entry point: `resources/css/app.css`, `resources/js/app.js` |
| `laravel/pail` | Local log tailing in dev workflow | Used in `composer run dev` |
| `pestphp/pest`, `mockery` | Testing | Test suites in `tests/Unit` + `tests/Feature` |

### Middleware Stack

Registered in `bootstrap/app.php`:
```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

Usage: `->middleware('permission:pos.access')` or `->middleware('role:admin')`

### Database Configuration

- **Driver**: SQLite
- **Location**: `database/database.sqlite`
- **Migrations**: In `database/migrations/`, includes Laravel base migrations (`0001_*`) plus app domain migrations (`2026_03_28_*` and `2026_04_*`)
- **Factories**: `UserFactory` and `ProductFactory` available for testing

### POS Terminal Model

The `PosTerminal` model (`app/Models/PosTerminal.php`, migration `2026_04_19_000001`) represents physical POS devices:
- **Fields**: `terminal_id` (unique integer), `terminal_name`, `branch_id` (FK)
- **Relationship**: `belongsTo(Branch::class)`
- **Usage**: Seeded in `database/seeders/PosTerminalSeeder.php`; referenced during checkout via session (`pos_terminal` key)

---

## Critical Developer Workflows

### POS Terminal Selection & Branch Scoping

**Context**: Before any POS operation, a terminal must be selected. The terminal defines the branch context for the entire session.

1. **Terminal Lookup** → User selects terminal before login (`Auth\TerminalSelectionController::create()` loads `PosTerminal::with('branch:id,name')->orderBy('terminal_id')`)
2. **Session Storage** → Terminal data stored in session under `pos_terminal` key with structure: `{ id, terminal_id, terminal_name, branch_id, branch_name }`
3. **Branch Resolution** → `CheckoutController::resolveTerminalBranchId()` extracts `branch_id` from session; throws 422 if missing/invalid. `AuthenticatedSessionController::create()` redirects to `terminal.select` if `pos_terminal` is missing
4. **Inventory Scoping** → All cart operations and stock checks filter by the terminal's branch via `BranchInventory.where('branch_id', $branchId)`

**Note**: Terminal selection UI persists the chosen terminal id in `sessionStorage` under `pos_terminal_id` for preselect (`resources/views/auth/select-terminal.blade.php`). Inventory controllers read `pos_terminal_id` from the server session for branch defaults (`Inventory\OverviewController`, `StockInController`, `StockOutController`, `StockMovementController`).

**Important**: If `pos_terminal` is not set in session, POS operations fail with "Terminal is not selected" error. Always ensure terminal is selected before rendering POS pages.

### Adding a New Module (e.g., "Reporting")

1. **Routes** (`routes/web.php`):
   ```php
   Route::middleware('auth')->group(function () {
       Route::get('/reporting/dashboard', fn() => view('modules.reporting.dashboard'))
           ->middleware('permission:reporting.view')->name('reporting.dashboard');
   });
   ```

2. **View** (`resources/views/modules/reporting/dashboard.blade.php`):
   ```blade
   <x-app-layout>
       <x-slot name="header"><h2>...</h2></x-slot>
       {{-- Content --}}
   </x-app-layout>
   ```

3. **Sidebar** (`resources/views/components/sidebar/_content.blade.php`):
   ```blade
   @can('reporting.view')
   <x-sidebar.item href="{{ route('reporting.dashboard') }}" 
       :active="request()->routeIs('reporting.*')">
       Reporting
   </x-sidebar.item>
   @endcan
   ```

4. **Permissions**: Ensure permission exists in database (check `permissions` table).

### Implementing Purchasing Workflows

**Product Standardization**: All product names in purchasing follow `UPPERCASE_WITH_UNDERSCORES` format (e.g., `HAMMER_CLAW_500G`, `NAIL_GALVANIZED_1_5_INCH`). Standardization happens automatically during inline product creation. See `PRODUCT_STANDARDIZATION.md` for complete rules and implementation.

**Inline Product Creation**: Users can create new products on-the-fly via modal during purchasing checkout:
- System provides text input for product name (auto-standardizes to uppercase with underscores)
- User selects unit from dropdown (pcs, box, meter, liter, kg, gram, or custom)
- User enters cost price (capital)
- Real-time preview shows standardized name and duplicate warning (case-insensitive check)
- Upon save, product is created with `status='active'` and added to cart immediately

**Implementation Files**:
- `app/Http/Controllers/Purchasing/ProductController.php` — `standardizeName()`, `validateUniqueness()`, `store()`, `preview()`
- `resources/views/modules/purchasing/new-invoice.blade.php` — Modal UI and Alpine.js logic

**Case-Insensitive Uniqueness Check**:
```php
$query = Product::whereRaw('UPPER(name) = ?', [strtoupper($standardized)]);
if ($excludeId) {
    $query->where('id', '!=', $excludeId);
}
$exists = $query->exists();
```

### Modifying POS Checkout Logic

**File**: `app/Http/Controllers/Pos/CheckoutController.php::finalize()`

**Critical**: Always wrap DB changes in `DB::transaction()` to ensure stock decrements are atomic.

**Stock Validation Pattern**:
```php
$inventories = BranchInventory::where('branch_id', $data['branch_id'])
    ->whereIn('product_id', $productIds)
    ->lockForUpdate()  // Prevent race conditions
    ->get()->keyBy('product_id');

if (!$inv || $inv->quantity < $qty) {
    abort(422, 'Insufficient stock for product '.$pid);
}
```

### Adding Product Search Filters

**File**: `app/Models/Product.php::scopeSearch()`

Current: Simple LIKE on `name` and `unit`. Extend with:
```php
public function scopeActive(Builder $query) {
    return $query->where('status', 'active');
}
// Usage: Product::search($q)->active()->get();
```

---

## Refactoring & Reusability Patterns

### Table UI Components Strategy

Instead of duplicating sort/filter/pagination UI across views, use reusable Blade components:

**Sorting**: Use `<x-table.sortable-header>` for every sortable column. Benefits:
- Consistent sort arrow UI across all tables
- Single source of truth for sort URL generation
- Eliminates duplicated route parameter handling

**Pagination**: Use `<x-table.pagination>` instead of inline pagination logic. Ensures:
- Consistent "Showing X to Y" text across all list views
- Accessible markup (aria-labels, rel attributes)
- Unified styling for page links

**Empty States**: Use `<x-table.empty-state>` to standardize the "no records found" message.

**Filters**: Use `<x-filters.branch-select>` for admin branch filtering. Automatically:
- Hides if only 1 branch exists
- Preserves other query parameters during filter submission
- Auto-submits on selection

### Before Creating a New Component

**Always check if a generic/reusable component already exists** before building a new one:

1. **Search existing components** in `resources/views/components/`:
   - Check `components/table/` for table-related UI
   - Check `components/filters/` for filter dropdowns
   - Check `components/` root for generic patterns
   - Use `grep` or IDE search: search for similar patterns in the codebase

2. **Review component props** to see if it's already flexible enough:
   - `<x-filters.dropdown-filter>` — Generic filter for ANY model/field (branches, suppliers, statuses, etc.)
   - `<x-table.sortable-header>` — Generic sortable column header for ANY sortable field
   - `<x-table.pagination>` — Generic pagination for any Paginator instance
   - `<x-product-search-typeahead>` — Reusable product search with keyboard nav

3. **Adapt existing component** if needed:
   - Add new props instead of creating duplicate
   - Example: `dropdown-filter` now handles ANY model via `valueField` and `displayField` props
   - This is faster and ensures consistent behavior across modules

4. **Only create new component if**:
   - No existing component solves the problem
   - The pattern appears in **2+ places** (DRY principle)
   - The component is genuinely domain-specific and not reusable elsewhere

### When to Extract Components

Extract a Blade component when the UI pattern appears **2+ times** across different views. Current extractions:
- **Sortable headers** → Used in `pos.transactions`, `inventory.overview` — `<x-table.sortable-header>`
- **Filter dropdowns** → Generic `<x-filters.dropdown-filter>` for branches, suppliers, statuses, payment methods, etc.
- **Pagination** → Used in all list views — `<x-table.pagination>`
- **Edit modal** → Used for suppliers, purchases, and other forms
- **Product search typeahead** → Used in purchasing, POS, and inventory — `<x-product-search-typeahead>`

### Extending Table Components

To add new sortable columns to existing tables, simply add a new `<x-table.sortable-header>`:
```blade
<x-table.sortable-header 
    label="Status"
    :sortBy="$sortBy"
    :sortDir="$sortDir"
    column="status"
    route="current.route.name"
    :params="['search' => $search]"
/>
```

No need to modify controller or add new logic—columns are defined in the view.

---

## Common Gotchas & Edge Cases

1. **Cart Persistence**: POS cart lives in session, NOT database. Transactions lost on session expiry.
2. **Floating-Point Math**: All monetary/quantity fields cast to `float`. Avoid decimals in totals without rounding.
3. **Branch Scoping**: `BranchInventory` is branch-specific. Always filter by `branch_id` in stock queries. For POS, this is resolved via `CheckoutController::resolveTerminalBranchId()` from the session's `pos_terminal.branch_id`.
4. **Permissions Depend on Seeding**: RBAC routes will 403 when DB is migrated without seeding; run seeder and verify `permissions` table.
5. **SaleItem Schema**: `SaleItem` migration only includes `sale_id`, `product_id`, `quantity`, `markup`, `subtotal`. Product details (`name`, `unit`, `unit_price`, `cost`) are NOT persisted in `SaleItem`; fetch from `Product` model on display.
6. **Terminal Branch Resolution**: `CheckoutController::resolveTerminalBranchId()` requires `pos_terminal.branch_id` in session. If missing or invalid (< 1), returns 422 error. Always validate terminal selection before POS operations.
7. **Vite Hot Reload**: Only works in development. Run `npm run dev` to enable CSS/JS changes without rebuilds.
8. **Sortable Header Params**: When using `<x-table.sortable-header>`, always pass critical query params (e.g., `search`, `branch_id`) in the `:params` prop to preserve them across sort clicks. Otherwise, filters reset when user sorts.
9. **Filter Dropdown Visibility**: `<x-filters.dropdown-filter>` checks `$items->count() >= $minCount` before rendering (default minCount=2). Control visibility with the `minCount` prop—use `minCount="1"` to always show, even with a single item.
10. **Filter Field Mapping**: Use `valueField` and `displayField` props on `dropdown-filter` to map model attributes to option values/labels. For nested/non-standard fields, use `data_get()` helper in your controller or model accessor.
11. **Payment Method Storage**: `Sale` model includes `payment_method` column (defaults to 'cash'). Currently only cash is supported, but field is extensible for future payment types.

---

## Quick Reference: File Locations

| Need | File(s) |
|------|---------|
| Add a route | `routes/web.php` (authenticated) or `routes/auth.php` (guest) |
| Create a model | `app/Models/{ModelName}.php` |
| New controller | `app/Http/Controllers/{Module}/{ControllerName}.php` |
| New view | `resources/views/modules/{module}/{view}.blade.php` |
| Sidebar menu | `resources/views/components/sidebar/_content.blade.php` |
| Permissions config | `config/permission.php` |
| Database schema | `database/migrations/{date}_{description}.php` |
| Tests | `tests/Unit/` or `tests/Feature/` (Pest syntax) |

---

## Initial Setup Commands

```bash
# Fresh start
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev  # In another terminal

# Run locally
php artisan serve        # http://localhost:8000
npm run dev             # Vite HMR for assets
# Or use combined dev stack (serve + queue + pail + vite)
composer run dev

# Testing
./vendor/bin/pest
./vendor/bin/pint --test
```

---

**Generated**: April 2026  
**Last Updated**: While analyzing codebase at commit scope  
**Audience**: AI agents and collaborative developers
