# Deliverable9 - Software Specification Requirements

## Purpose
Define the functional and non-functional requirements for the RNM Hardware Management System (RNM-HMS) as implemented in this repository.

## Scope
The system covers point-of-sale (POS), inventory management, purchasing/invoicing, supplier management, audit log views, terminal selection, and role-based access control (RBAC). It is a Laravel 13 web application using Blade templates, Tailwind CSS, and Alpine.js, backed by SQLite.

## Users and Roles
- **Admin**: Full access to modules and multi-branch data.
- **Manager**: Sales, inventory, purchasing, reports, audit log access based on seeded permissions.
- **Cashier**: POS access, sales history, receipt printing.

Permissions are seeded in `database/seeders/DatabaseSeeder.php` and enforced via route middleware `permission:*`.

## System Overview
- **Frontend**: Blade templates + Tailwind CSS + Alpine.js.
- **Backend**: Laravel controllers in `app/Http/Controllers`.
- **Database**: SQLite file at `database/database.sqlite`.
- **Session State**:
  - POS terminal selection stored in `pos_terminal` (server session).
  - POS cart stored in `pos_cart`.
  - Purchasing cart stored in `purchasing_cart`.

## Functional Requirements

### FR-AUTH-001 Terminal Selection
- The system shall require a POS terminal selection before POS actions.
- The selection shall store terminal details in session `pos_terminal` with `branch_id` for branch scoping.

### FR-POS-001 POS Access
- The system shall provide a POS page at `/pos` protected by `permission:pos.access`.

### FR-POS-002 Product Search and Browse
- The system shall expose POS product search and browse endpoints under `/pos/api/products/*`.
- Results shall include branch-scoped availability using `BranchInventory` for the terminal branch.

### FR-POS-003 Cart Management
- The system shall allow adding, updating, removing items in the POS cart via `/pos/api/cart/*`.
- The POS cart shall be stored in session `pos_cart`.

### FR-POS-004 Checkout Prepare
- The system shall provide a checkout preview endpoint `/pos/api/checkout/prepare`.
- It shall hydrate cart items with `Product` details and compute totals using `Product.capital` as unit price.

### FR-POS-005 Checkout Finalize
- The system shall finalize sales at `/pos/api/checkout/finalize` in a database transaction.
- It shall lock branch inventory rows and validate stock before decrement.
- It shall create `Sale` and `SaleItem` records and clear `pos_cart`.
- It shall return a receipt payload for UI preview/printing.

### FR-INV-001 Inventory Overview
- The system shall provide a stock overview page at `/inventory/overview`.
- Access shall be protected by `permission:inventory.view-overview`.

### FR-INV-002 Manual Stock-In
- The system shall provide a manual stock-in page and API endpoints:
  - `/inventory/manual-stock-in` (view)
  - `/inventory/api/stock-in/store` (store)
- Stock-in shall increment `BranchInventory` and create `InventoryMovement` entries.

### FR-INV-003 Manual Stock-Out
- The system shall provide a manual stock-out page and API endpoints:
  - `/inventory/stock-out` (view)
  - `/inventory/api/stock-out/store` (store)
- Stock-out shall validate available stock, decrement `BranchInventory`, and create `InventoryMovement` entries.

### FR-INV-004 Branch Scoping for Non-Admin Users
- Non-admin users shall only stock-in/out for the branch mapped to the session `pos_terminal_id`.

### FR-INV-005 Stock Movements
- The system shall provide a stock movements page at `/inventory/stock-movements`.
- Access shall be protected by `permission:inventory.view-movements`.

### FR-PUR-001 Purchasing Workflow
- The system shall provide a new invoice page at `/purchasing/new-invoice`.
- It shall provide purchasing cart endpoints under `/purchasing/api/cart/*`.
- It shall provide checkout endpoints `/purchasing/api/checkout/prepare` and `/purchasing/api/checkout/finalize`.

### FR-PUR-002 Purchasing Finalize
- Purchasing finalize shall create `Purchase`, `PurchaseDetail`, and `Invoice` records atomically.
- It shall increment `BranchInventory` for purchased items and clear `purchasing_cart`.
- It shall compute `date_due` using a configurable `date_due_offset` (default 30 days).

### FR-PUR-003 Inline Product Creation
- The system shall allow creating products during purchasing via `/purchasing/api/products/store`.
- Product names shall be standardized to `UPPERCASE_WITH_UNDERSCORES` and validated case-insensitively for uniqueness.

### FR-SUP-001 Supplier Management
- The system shall provide supplier CRUD routes via `Route::resource('suppliers', ...)`.
- Access shall be protected by `permission:suppliers.view` and related permissions from the seeder.

### FR-AUD-001 Audit Logs Views
- The system shall provide audit log routes:
  - `/audit-logs/user-activity`
  - `/audit-logs/system-logs`
  - `/audit-logs/archives`
- Access shall be protected by `permission:audit.user-activity.view` and `permission:audit.system-logs.view` as configured in routes.

### FR-DASH-001 Dashboard
- The system shall provide a dashboard view at `/dashboard` protected by `auth` and `verified` middleware.

### FR-PROF-001 Profile Management
- The system shall provide profile edit/update/delete routes under `/profile` for authenticated users.

## Data Model Requirements
Key entities and relationships (see `app/Models` and `database/migrations`):
- **Branch** has many **BranchInventory** and **PosTerminal**.
- **Product** is referenced by **BranchInventory**, **SaleItem**, and **PurchaseDetail**.
- **Sale** has many **SaleItem** and belongs to **User** and **Branch**.
- **Purchase** has many **PurchaseDetail** and one **Invoice**.
- **InventoryMovement** records stock changes with `type` and `quantity_change`.

## Business Rules and Constraints
- POS pricing uses `Product.capital` as the unit price.
- POS and Purchasing carts are session-based and not persisted to the database.
- Cash-only payments are supported in POS (`payment_method = cash`).
- Branch scoping is enforced using terminal selection (`pos_terminal.branch_id`).
- Product names created via purchasing are standardized and case-insensitive for uniqueness.

## Non-Functional Requirements
- **Consistency**: POS checkout and purchasing finalize must be atomic transactions.
- **Security**: Route access must use permission middleware for module actions.
- **Performance**: Product search endpoints should cap result size (see controllers for limits).
- **Maintainability**: Use shared Blade components for tables, pagination, and filters.

## External Interfaces
- **Web UI**: Blade views under `resources/views/modules`.
- **API Endpoints**: Module-specific endpoints under `/pos/api`, `/inventory/api`, and `/purchasing/api`.

## Assumptions
- SQLite is used for local development and demo environments.
- Users are assigned roles and permissions by seeders or admin operations.

## Out of Scope / Known Limitations
- Card/check payments (cash-only POS).
- Barcode scanning integration.
- Persistent carts across sessions.

## References
- `AGENTS.md`
- `README.md`
- `routes/web.php`
- `database/seeders/DatabaseSeeder.php`
- `PRODUCT_STANDARDIZATION.md`

