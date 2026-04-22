# Table UI & Branch Filtering Components - Implementation Guide

This document describes the reusable Blade components created for table UI and branch filtering.

## Components Created

### 1. `<x-table.sortable-header>` 
**Location**: `resources/views/components/table/sortable-header.blade.php`

Reusable sortable column header with directional arrows.

**Props**:
- `label` (string) - Column header label
- `sortBy` (string) - Current sort column name
- `sortDir` (string) - Current sort direction ('asc' or 'desc')
- `column` (string) - Database column name for sorting
- `route` (string) - Route name to generate links
- `params` (array) - Additional query parameters to preserve (e.g., `['search' => $search]`)
- `align` (string) - Text alignment: 'left' (default), 'center', 'right'

**Features**:
- Displays up/down arrow when column is active
- Shows up/down arrow icon outline when column is inactive (always visible)
- Preserves other query parameters (search, filters) when sorting
- Smooth color transitions on hover
- Supports text alignment options

**Example Usage**:
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

---

### 2. `<x-table.pagination>`
**Location**: `resources/views/components/table/pagination.blade.php`

Reusable pagination UI with results count and page navigation.

**Props**:
- `paginator` (Paginator) - Laravel paginator instance

**Features**:
- Shows "Showing X to Y of Z results" text
- Previous/Next navigation buttons (disabled when not applicable)
- Page number links
- Accessible markup with aria-labels and rel attributes
- Only renders if paginator has pages

**Example Usage**:
```blade
<x-table.pagination :paginator="$transactions" />
```

---

### 3. `<x-table.empty-state>`
**Location**: `resources/views/components/table/empty-state.blade.php`

Reusable empty table message component.

**Props**:
- `colspan` (int) - Number of columns to span
- `message` (string) - Message to display

**Features**:
- Centered, styled empty state message
- Handles colspan for proper table layout

**Example Usage**:
```blade
@empty
    <x-table.empty-state 
        :colspan="8" 
        message="No inventory records found. Try adjusting your search filters."
    />
@endforelse
```

---

### 4. `<x-filters.branch-select>`
**Location**: `resources/views/components/filters/branch-select.blade.php`

Admin-only branch filter dropdown for filtering data by branch.

**Props**:
- `branches` (Collection) - Branch collection
- `selected` (int|null) - Currently selected branch_id
- `route` (string) - Route name to submit form to
- `params` (array) - Additional query parameters to preserve
- `label` (string) - Dropdown label (default: 'Filter by Branch')

**Features**:
- Only renders if multiple branches exist (`$branches->count() > 1`)
- Auto-submits form on selection change
- Preserves other query parameters (search, sort_by, sort_dir)
- Hidden inputs automatically added for all params
- Accessible form with aria-label

**Example Usage**:
```blade
<x-filters.branch-select 
    :branches="$allBranches"
    :selected="$filterBranchId"
    route="inventory.overview"
    :params="['search' => $search, 'sort_by' => $sortBy, 'sort_dir' => $sortDir]"
    label="Filter by Branch"
/>
```

---

## Where These Components Are Used

### Transaction History (`resources/views/modules/pos/transactions.blade.php`)
- Uses `<x-table.sortable-header>` for: ID, Date, Total Amount, Payment Method
- Uses `<x-table.pagination>` for pagination
- Uses `<x-table.empty-state>` for empty state

### Stock Overview (`resources/views/modules/inventory/stock-overview.blade.php`)
- Uses `<x-filters.branch-select>` for admin branch filtering
- Uses `<x-table.sortable-header>` for: Product Name, Quantity
- Uses `<x-table.pagination>` for pagination
- Uses `<x-table.empty-state>` for empty state

---

## Benefits

1. **DRY Principle**: Eliminates duplicate sorting/pagination/filtering UI code across views
2. **Consistency**: All tables have uniform sorting arrows, pagination styling, and behavior
3. **Maintainability**: Bug fixes or styling changes apply to all tables automatically
4. **Accessibility**: Proper ARIA labels and semantic HTML built-in
5. **Extensibility**: Easy to add new sortable columns or filters without modifying components
6. **Code Reduction**: Previous views had ~30 lines of sort/pagination HTML; now 2-3 lines per table

---

## Adding New Tables

When creating a new list view with sorting/pagination:

1. **Use `<x-table.sortable-header>` for sortable columns:**
   ```blade
   <thead>
       <tr>
           <x-table.sortable-header 
               label="Status"
               :sortBy="$sortBy"
               :sortDir="$sortDir"
               column="status"
               route="current.route.name"
               :params="['search' => $search]"
           />
       </tr>
   </thead>
   ```

2. **Use `<x-table.empty-state>` for empty results:**
   ```blade
   @empty
       <x-table.empty-state :colspan="X" message="Your message" />
   @endforelse
   ```

3. **Use `<x-table.pagination>` at the bottom:**
   ```blade
   <x-table.pagination :paginator="$results" />
   ```

4. **Use `<x-filters.branch-select>` if admin filtering needed:**
   ```blade
   @if($isAdmin)
       <x-filters.branch-select 
           :branches="$allBranches"
           :selected="$filterBranchId"
           route="current.route.name"
           :params="['search' => $search]"
       />
   @endif
   ```

---

## Key Implementation Details

### Sortable Header Arrow Logic
- **Active column**: Shows solid directional arrow (blue)
- **Inactive columns**: Shows outline arrow (gray)
- Arrows always visible, eliminating visual clutter when not sorting

### Branch Filter Visibility
- Only renders if `$branches->count() > 1`
- For single-branch systems, the dropdown is completely hidden
- For multi-branch systems, shows "Filter by Branch" dropdown with "All Branches" default

### Parameter Preservation
- All components properly preserve query parameters
- When sorting, search/filter values stay intact
- When filtering, sort/search values stay intact
- This prevents "filter reset" issues common in list views

---

## Testing

To verify components work correctly:

1. Navigate to `/pos/transactions` - verify sortable headers, pagination, empty state
2. Navigate to `/inventory/overview` - verify sortable headers, branch filter (if admin), pagination
3. Click sortable headers - verify sort direction changes and parameters preserved
4. Use branch filter (admin only) - verify it filters correctly and other params preserved
5. Navigate pages - verify pagination links work correctly

---

**Created**: April 22, 2026  
**Status**: All components implemented and integrated into existing views

