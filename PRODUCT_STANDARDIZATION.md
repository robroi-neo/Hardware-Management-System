# Product Standardization Guide

## Overview

This document defines the standardization rules for hardware product names in the RNM Hardware Management system. These rules ensure consistency, prevent duplicates, and improve searchability across the inventory and purchasing modules.

## Naming Convention

### Format

Product names follow a **UPPERCASE_WITH_UNDERSCORES** convention with optional descriptive suffixes:

```
CATEGORY_SPECIFIC_DESCRIPTOR_SIZE_VARIANT
```

### Examples

- `HAMMER_CLAW_500G` — Hammer, claw type, 500g
- `NAIL_GALVANIZED_1_5_INCH` — Galvanized nail, 1.5 inches
- `WOOD_SCREW_STAINLESS_3MM_X_25MM` — Stainless wood screw, 3mm x 25mm
- `DRILL_BIT_STEEL_10MM` — Steel drill bit, 10mm diameter
- `PAINT_BRUSH_SYNTHETIC_2_INCH` — Synthetic paint brush, 2 inches

### Rules

1. **Uppercase with Underscores**: All product names must be uppercase letters separated by underscores
   - ✅ `HAMMER_CLAW`
   - ❌ `Hammer Claw` or `hammer-claw`

2. **No Special Characters**: Only alphanumeric and underscores allowed
   - ✅ `NAIL_3MM`
   - ❌ `NAIL (3MM)` or `NAIL-3MM`

3. **Spaces as Underscores**: When specifying dimensions or variants, use underscores and preserve precision
   - ✅ `WOOD_SCREW_3MM_X_25MM`
   - ❌ `WOOD_SCREW 3mm x 25mm`

4. **No Unit Symbols**: Units are implicitly part of the name, or stored in the `unit` field
   - ✅ `NAIL_1_5_INCH` or `NAIL` with `unit='1.5 inch'`
   - ❌ `NAIL_1.5"` or `NAIL_1.5IN`

## Uniqueness Validation

### Case-Insensitive Matching

Product names are validated for uniqueness **case-insensitively**. The system treats these as duplicates:

- `HAMMER_CLAW` and `hammer_claw` → **Same product**
- `NAIL_GALVANIZED` and `Nail_Galvanized` → **Same product**

### Implementation

When creating or updating a product:

1. **Normalize Input**: Convert product name to uppercase with underscores
   ```php
   $standardized = strtoupper(trim($input));
   $standardized = preg_replace('/[^A-Z0-9_]/', '_', $standardized);
   $standardized = preg_replace('/_+/', '_', $standardized);
   ```

2. **Check Uniqueness**: Query the database case-insensitively
   ```php
   $exists = Product::whereRaw('UPPER(name) = ?', [strtoupper($standardized)])
       ->exists();
   ```

3. **Validation Error**: Return error if duplicate found
   ```
   "Product name '{$input}' already exists (standardized as {$standardized})"
   ```

## Inline Product Creation (Purchasing Module)

During purchasing checkout, users may create products on-the-fly via a modal:

### Required Fields

1. **Product Name** (required)
   - User enters name (e.g., "Hammer Claw")
   - System auto-standardizes to uppercase/underscores
   - System validates case-insensitive uniqueness

2. **Unit** (required, default='pcs')
   - User selects from dropdown or enters custom
   - Examples: `pcs`, `box`, `meter`, `liter`, `kg`

3. **Capital (Cost)** (required)
   - User enters wholesale/cost price
   - Stored as `Product.capital` (float)

### Validation

- **Product Name**: Required, max 255 chars, must standardize to valid format
- **Unit**: Required, max 50 chars
- **Capital**: Required, must be numeric > 0
- **Uniqueness**: Case-insensitive check against existing products

## Selling Price

The `Product` model does **not** have a separate `selling_price` column. Instead:

- **In POS**: Use `Product.capital` as the sale price (configurable per session/transaction)
- **In Purchasing**: Allow override of cost during checkout (`PurchaseDetail.unit_price`)
- **Future Enhancement**: Add `markup_percentage` column to `Product` for auto-calculating selling price

## Reference Implementation

See `app/Http/Controllers/Purchasing/ProductController.php` for:
- `standardizeName()` — Normalizes input to standard format
- `validateUniqueness()` — Case-insensitive uniqueness check
- `create()` — Creates product with standardization

See `resources/views/modules/purchasing/new-invoice.blade.php` for:
- Product creation modal markup and Alpine.js logic
- Real-time name standardization preview
- Unit and capital input fields

---

**Last Updated**: May 4, 2026  
**Version**: 1.0

