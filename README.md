# RNM Hardware Management System

A comprehensive Laravel-based business management system for hardware retail operations, featuring point-of-sale (POS), inventory management, purchasing, and audit logging capabilities.

## 🎯 Features

### Point of Sale (POS) System
- **Session-based shopping cart** - Add/remove/update items before checkout
- **Product search & browsing** - Real-time product discovery with stock availability
- **Instant checkout** - Process sales with automatic inventory decrement
- **Receipt generation** - Digital receipts with sale details and print capability
- **Cash-only payment** - Simplified payment method for MVP
- **Sales history tracking** - View past transactions with sortable columns
- **Stock validation** - Prevents overselling with real-time stock checks

### Inventory Management
- **Multi-branch support** - Manage inventory across multiple store locations
- **Manual Stock-In** - Add inventory with purchase/invoice reference
- **Manual Stock-Out** - Remove inventory with reason tracking (sale, transfer, adjustment)
- **Stock Overview** - Real-time inventory levels per branch
- **Stock Movements** - Complete audit trail of all inventory changes
- **Low stock alerts** - Visual indicators for items below thresholds
- **Branch-based filtering** - Admin view of all branches, staff limited to assigned branch

### Terminal Management
- **POS Terminal Selection** - Choose terminal before login (stored in session)
- **Branch-scoped access** - Terminal determines user's branch context
- **Session persistence** - Terminal selection persists across authenticated session
- **Multi-terminal ready** - Support for multiple terminals per branch

### Security & Access Control
- **Role-Based Access Control (RBAC)** - Using Spatie Permission package
- **Permission-based routes** - Fine-grained control per module/action
- **Branch scoping** - Non-admin users see only their branch's data
- **User attribution** - All inventory movements tracked to user
- **Audit logging ready** - Foundation for activity tracking

## 📊 Core Modules

### POS Module (`/pos`)
- **New Sale** - Create and process transactions
- **Transactions** - View sale history with filters and sorting
- **Features**: Product search, cart management, checkout, receipt generation

### Inventory Module (`/inventory`)
- **Overview** - Dashboard view of stock levels
- **Manual Stock-In** - Add inventory from purchases
- **Manual Stock-Out** - Remove inventory with reason
- **Stock Movements** - Complete movement history with filtering

### Purchasing Module (`/purchasing`)
- **New Invoice** - Create supplier invoices (placeholder)
- **Invoice History** - Track purchase orders (placeholder)

### Audit Logs Module (`/audit-logs`)
- **User Activity** - Track user actions (placeholder)
- **System Logs** - System event tracking (placeholder)

### Suppliers Module (`/suppliers`)
- **Supplier Management** - Manage supplier information (placeholder)

## 🛠 Tech Stack

| Component | Technology |
|-----------|-----------|
| **Language** | PHP 8.3+ |
| **Framework** | Laravel 13 |
| **Database** | SQLite (single file) |
| **Frontend** | Blade templates + Tailwind CSS 3 + Alpine.js 3 |
| **Asset Bundling** | Vite 8 + laravel-vite-plugin |
| **Auth** | Laravel Breeze (session-based) |
| **RBAC** | spatie/laravel-permission 8 |
| **Testing** | Pest 4 (PHPUnit 12) |
| **Code Formatting** | Pint (PSR-12) |

## 🚀 Getting Started

### Prerequisites

- **PHP 8.3+** with extensions: `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `bcmath`, `curl`
- **Composer 2.x** — use the latest release (`composer self-update`). Composer 2.2 LTS crashes while running Laravel 13's post-install scripts.
- **Node.js** `^20.19.0` or `>=22.12.0` (required by Vite 8) and npm

### Installation

```bash
# Clone repository
git clone <repository-url>
cd hardware-management-system

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build
```

### Running Locally

```bash
# Terminal 1: Start PHP server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev

# Or use combined dev stack
composer run dev
```

Visit `http://localhost:8000`, click **Try the live demo**, pick a role and a sample branch. There is no login: visitors are signed in as that role's demo user.

## 🧪 Live Demo

The public deployment is demo-only. The landing page (`/`) leads to `/demo` (pick a role), `/demo/branch` (pick a sample branch), and into the app as the demo user for that role.

| Role | Demo user | Lands on |
|------|-----------|----------|
| Admin | Demo Admin (`09000000001`) | Dashboard |
| Manager | Demo Manager (`09000000003`) | Dashboard |
| Cashier | Demo Cashier (`09000000002`) | POS |

- **Sample data:** `php artisan migrate:fresh --seed` loads the placeholder catalogs from `database/seeders/data/catalog.csv` (edit it in a spreadsheet) and about four weeks of sales, refunds and purchases.
- **In the app:** an amber banner shows the role, branch and next reset, with *Try these*, *Switch role*, *Reset data* and *Exit demo*. Demo accounts can't be edited.
- **Settings:** `config/demo.php`.

### Hourly reset

```bash
php artisan demo:snapshot   # build the clean copy (database/demo-snapshot.sqlite)
php artisan demo:reset      # put the live database back to it now
```

On the host, set `DEMO_AUTO_RESET=true` (it is off by default so a local database is never overwritten). The data then resets every `DEMO_RESET_MINUTES` (60):

- if the host runs `php artisan schedule:run` every minute, on schedule;
- otherwise, on the first visit after the interval.

Each reset moves the history forward so the latest sale is a few minutes old. Keep `SESSION_DRIVER=file` and `CACHE_STORE=file` (as in `.env.example`) so visitors stay signed in across a reset. After changing seeders or the catalog, run `demo:snapshot` again.

## 📝 Project Structure

```
rnm-hardware-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Pos/              # POS module controllers
│   │   │   ├── Inventory/        # Inventory controllers
│   │   │   └── Auth/             # Authentication + Terminal selection
│   │   └── Requests/             # Form validation requests
│   ├── Models/
│   │   ├── Sale                  # POS transaction
│   │   ├── SaleItem              # Individual sale items
│   │   ├── Product               # Hardware catalog
│   │   ├── BranchInventory       # Per-branch stock
│   │   ├── InventoryMovement     # Audit trail
│   │   ├── Branch                # Store locations
│   │   ├── PosTerminal           # Terminal definitions
│   │   └── Invoice               # Purchase orders
│   └── ...
├── database/
│   ├── migrations/               # Schema definitions
│   ├── seeders/                  # Initial data
│   └── database.sqlite           # SQLite database
├── resources/
│   ├── views/
│   │   ├── modules/              # Module-specific views
│   │   ├── components/           # Reusable Blade components
│   │   └── layouts/              # Main layout templates
│   ├── css/                      # Tailwind CSS
│   └── js/                       # Alpine.js & utilities
├── routes/
│   ├── web.php                   # Web routes (authenticated)
│   └── auth.php                  # Auth routes (guest)
├── tests/                        # Pest tests
└── config/
    └── permission.php            # RBAC configuration
```

## 🔧 Configuration

### Environment Variables
```env
APP_NAME="RNM Hardware Management"
APP_ENV=local
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/database.sqlite
```

### Database
SQLite database is stored in `database/database.sqlite` - no additional setup required.

## 📚 API Endpoints

### POS API
- `GET /pos/api/products/search` - Search products
- `GET /pos/api/products/browse` - Browse with pagination
- `GET /pos/api/cart` - Get cart items
- `POST /pos/api/cart/add` - Add to cart
- `POST /pos/api/cart/update` - Update cart item
- `POST /pos/api/cart/remove` - Remove from cart
- `GET /pos/api/checkout/prepare` - Prepare checkout
- `POST /pos/api/checkout/finalize` - Complete sale

### Inventory API
- `GET /inventory/api/products/search` - Search products for stock operations
- `POST /inventory/api/stock-in/store` - Process stock-in
- `POST /inventory/api/stock-out/store` - Process stock-out

## 🔐 Permissions

### Default Roles & Permissions
```
Admin Role
├── pos.access                    # Use POS
├── sales.view-history           # View sales
├── inventory.view-overview       # View stock levels
├── inventory.update              # Stock in/out
├── inventory.view-movements      # View movement history
├── purchases.create              # Create invoices
├── purchases.view-history        # View invoices
├── audit.user-activity.view      # View user logs
└── suppliers.view                # View suppliers

Cashier Role
├── pos.access                    # Use POS
├── sales.view-history           # View own/branch sales
└── inventory.view-overview       # View stock levels

Manager Role
├── [All Cashier permissions]
├── inventory.update              # Stock in/out
├── inventory.view-movements      # View movements
└── purchases.view-history        # View invoices
```

## 🎨 UI Components

### Reusable Blade Components
- **`<x-table.sortable-header>`** - Sortable column headers with arrow indicators
- **`<x-table.pagination>`** - Pagination with results summary
- **`<x-table.empty-state>`** - Standardized empty state message
- **`<x-filters.branch-select>`** - Admin branch filter dropdown
- **`<x-sidebar.container>`** - Navigation sidebar
- **`<x-sidebar.item>`** - Menu items with active state
- **`<x-sidebar.dropdown>`** - Collapsible menu groups

## 💾 Database Schema

### Key Tables
- **users** - User accounts with roles
- **sales** - POS transactions
- **sale_items** - Individual items in sales
- **products** - Hardware catalog
- **branch_inventory** - Stock per branch
- **inventory_movements** - Audit trail of all stock changes
- **branches** - Store locations
- **pos_terminals** - Terminal definitions
- **invoices** - Purchase orders
- **roles & permissions** - RBAC tables (Spatie)

## 🧪 Testing

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/Pos/

# Run with coverage
./vendor/bin/pest --coverage
```

## 📋 Development Commands

```bash
# Format code (PSR-12)
./vendor/bin/pint

# Check formatting without changes
./vendor/bin/pint --test

# Database management
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Reset & reseed (⚠️ destructive)
php artisan tinker                   # Interactive shell

# Cache management
php artisan cache:clear
php artisan config:clear
php artisan view:cache
```

## 🚦 Workflow Examples

### Processing a Sale
1. User selects terminal → logs in
2. Navigates to `/pos`
3. Searches for products
4. Adds items to cart
5. Reviews cart in sidebar
6. Clicks "Complete Sale"
7. System validates stock
8. Creates Sale & SaleItem records
9. Decrements BranchInventory
10. Shows receipt with print option
11. Movement recorded for audit

### Restocking Inventory
1. Admin navigates to `/inventory/manual-stock-in`
2. Selects branch to restock
3. Searches for product
4. Enters quantity received
5. Selects reference type (purchase/invoice)
6. Submits form
7. System checks product exists
8. Increments BranchInventory
9. Creates InventoryMovement record
10. Records in Stock Movements view

## 📦 Dependencies

### PHP — production (`composer.json` → `require`)

| Package | Version | Purpose |
|---------|---------|---------|
| `php` | ^8.3 | Runtime |
| `laravel/framework` | ^13.38 | Application framework |
| `laravel/tinker` | ^3.0.2 | REPL (`php artisan tinker`) |
| `spatie/laravel-permission` | ^8.3 | Roles & permissions (RBAC) |

### PHP — development (`composer.json` → `require-dev`)

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/breeze` | ^2.4.2 | Authentication scaffolding |
| `laravel/pail` | ^1.2.7 | Log tailing (used by `composer run dev`) |
| `laravel/pint` | ^1.32 | Code formatter |
| `pestphp/pest` | ^4.7 | Test framework |
| `pestphp/pest-plugin-laravel` | ^4.1 | Laravel helpers for Pest |
| `mockery/mockery` | ^1.6.15 | Mocking |
| `fakerphp/faker` | ^1.24 | Fake data for factories/seeders |
| `nunomaduro/collision` | ^8.9.5 | CLI error reporting |

### Frontend (`package.json` → `devDependencies`)

| Package | Version | Purpose |
|---------|---------|---------|
| `vite` | ^8.3.5 | Dev server & bundler |
| `laravel-vite-plugin` | ^3.2.0 | Laravel ↔ Vite integration |
| `tailwindcss` | ^3.4.19 | Utility CSS (v3, run through PostCSS) |
| `@tailwindcss/forms` | ^0.5.11 | Form element styles |
| `postcss` | ^8.5.31 | CSS processing |
| `autoprefixer` | ^10.6.1 | Vendor prefixes |
| `alpinejs` | ^3.17.5 | Frontend interactivity |
| `axios` | ^1.20.0 | HTTP client |
| `concurrently` | ^9.2.5 | Runs the `composer run dev` stack |

> **Tailwind is pinned to v3.** v4 replaces `tailwind.config.js`/PostCSS with a CSS-first setup and needs a dedicated migration. Until then, `npm audit` reports 3 moderate advisories in Tailwind 3's build-time CSS parser (`postcss-selector-parser`). That code only runs when assets are built and is not shipped to browsers.

See `composer.json` and `package.json` for the source of truth.

## 🐛 Known Limitations (MVP)

- Cash-only payment method (no card/check support)
- Session-based cart (lost on logout or timeout)
- Single-file SQLite database (not for production)
- Placeholder purchasing and audit modules
- No barcode scanning integration
- Manual stock adjustments don't track reason initially

## 🔄 Recent Updates (April 2026)

✅ Terminal selection before login  
✅ Branch-scoped inventory operations  
✅ Manual stock-in with movement tracking  
✅ Manual stock-out with validation  
✅ Stock movements history with filtering  
✅ Reusable table UI components  
✅ POS receipt generation and printing  
✅ Permission-based access control  

## 📄 Documentation

- **AGENTS.md** - AI agent guidance for development
- **COMPONENTS.md** - Blade component reference
- **BEFORE_AFTER.md** - Code refactoring benefits

## 🤝 Contributing

1. Check existing permissions in `AGENTS.md`
2. Follow Laravel conventions (migrations, models, controllers)
3. Use reusable Blade components for UI
4. Run `pint` before committing
5. Add tests for new features

## 📞 Support

For questions about the RNM Hardware Management System, refer to:
- `AGENTS.md` for architecture decisions
- `COMPONENTS.md` for UI component usage
- Code comments for implementation details

## 📄 License

This project is proprietary software. All rights reserved.

---

**Last Updated**: October 26, 2026  
**Version**: 1.0.0 (MVP)  
**Status**: Active Development

