# Importer reference

Every module in Syncore that maintains a list of records (customers,
products, accounts, employees, etc.) ships with an Excel/CSV importer.
This page is the operator-facing reference for what each importer
expects, what column does the matching when records already exist, and
which fields default if you leave them blank.

If you're trying to figure out why a row failed — open the *Error CSV*
the import modal offers to download. Each row in that CSV tells you the
original row number, the failing column, and the rule it broke.

## How the upload flow works

1. Click *Import* on any index page. Pick an `.xlsx`, `.xls`, or
   `.csv` file (max 10 MB).
2. Maatwebsite reads the first row as the column header. The header
   names are case-sensitive and must match the column names listed
   below.
3. Each row is validated against that importer's rules. **Invalid rows
   are collected and reported; valid rows continue to import.** (Before
   `570e75a`, the first invalid row aborted the rest — this is the
   collect-and-continue behavior that landed in that commit.)
4. After the run, the modal shows a *Row | Field | Message* table of
   every validation failure. Click *Download errors (CSV)* to get the
   failed rows back with the original column values + the failing-rule
   message — open in Excel, fix the bad cells, re-upload the SAME
   file structure (header row + the failed data rows) and only those
   are re-processed.

## Shared conventions

- **Date columns** accept ISO (`2026-05-14`), US (`05/14/2026`),
  Indonesian (`14/05/2026`), Excel serial numbers, and most other
  natural formats. Internally they pass through `Carbon::parse()` via
  `HasImportTracking::parseDate()`.
- **Number columns** accept plain numbers, US format
  (`1,234.56`), European format (`1.234,56`), and strings with
  currency symbols (`Rp 1,234,500` — the prefix is stripped). See
  `HasImportTracking::parseNumber()`.
- **Boolean columns** accept `1` / `0` / `true` / `false` / `yes` /
  `no` (case-insensitive).
- **Foreign-key lookups by name**: when an importer says
  `category → resolved to categories.name`, you pass the *name* of
  the related record in that column — the importer looks it up at
  import time. Names not found cause the row to be skipped.
- **Lookup-vs-insert**: most importers have an *upsert key* (usually
  `code`, `email`, `sku`, or the record's number). If you provide a
  value for that column and an existing record matches, the row
  *updates* the existing record. If no match, it *creates a new* one.
  Importers marked `insert-only (no update path)` always create new
  records — duplicates are your problem.
- **Skip rule**: most importers silently skip rows where the
  `name`/required-key column is empty. This is by design — partial
  blank rows at the bottom of a spreadsheet shouldn't cascade into
  errors.

For developers / contributors, the per-importer code lives under
[`app/Imports/`](../app/Imports). Shared parsing and structured-error
collection live in
[`app/Imports/Concerns/HasImportTracking.php`](../app/Imports/Concerns/HasImportTracking.php).
The Livewire upload + error-table + CSV-download plumbing lives in
[`app/Livewire/Concerns/WithImport.php`](../app/Livewire/Concerns/WithImport.php).

---

## Sales

### `CustomersImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `email`, `phone`, `company`, `address`, `city`, `state`, `postal_code`, `country` (default `Indonesia`), `tax_id`, `notes` |
| Upsert key | `email` (exact match) |
| Foreign keys | — |
| Skip when empty | `name` |

### `SalesOrdersImport`

| Field | Behavior |
| --- | --- |
| Required | `customer` |
| Optional | `order_number`, `order_date`, `expected_delivery_date`, `status`, `payment_terms`, `subtotal`, `tax`, `discount`, `total`, `notes`, `terms`, `shipping_address` |
| Upsert key | `order_number` (exact match), only when provided |
| Foreign keys | `customer` → `customers.name` |
| Skip when | `customer` value can't be resolved |

### `PaymentTermsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name if missing), `days`, `description`, `is_active`, `sort_order` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | — |
| Skip when empty | `name` |

### `TaxesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name if missing), `rate`, `type` (`percentage` / `fixed`), `scope` (`sales` / `purchase` / `both`), `is_active`, `include_in_price`, `description` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | — |
| Skip when empty | `name` |

### `PricelistsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name), `currency` (default `IDR`), `type`, `discount`, `start_date`, `end_date`, `is_active`, `description` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | — |
| Skip when empty | `name` |

### `PromotionsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code`, `type`, `priority`, `is_combinable`, `requires_coupon`, `start_date`, `end_date`, `usage_limit`, `usage_per_customer` (alias: `per_customer`), `min_order_amount`, `min_quantity`, `is_active` (alias: `status`), `description`, `reward_type`, `discount_value`, `max_discount`, `buy_quantity`, `get_quantity`, `apply_to` |
| Upsert key | `code` (case-insensitive, uppercased), fallback to `name` |
| Foreign keys | — |
| Skip when empty | `name` |

### `SalesTeamsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `leader`, `description`, `target_amount`, `is_active` |
| Upsert key | `name` (case-insensitive) |
| Foreign keys | `leader` → `users.name` |
| Skip when empty | `name` |

---

## Purchase

### `SuppliersImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `email`, `phone`, `company`, `address`, `city`, `state`, `postal_code`, `country` (default `Indonesia`), `tax_id`, `bank_name`, `bank_account`, `notes` |
| Upsert key | `email` (exact match) |
| Foreign keys | — |
| Skip when empty | `name` |

### `PurchaseRfqsImport`

| Field | Behavior |
| --- | --- |
| Required | `supplier` |
| Optional | `reference`, `order_date`, `expected_arrival`, `status`, `subtotal`, `tax`, `total`, `notes` |
| Upsert key | `reference` (exact match), only when provided |
| Foreign keys | `supplier` → `suppliers.name` |
| Skip when | `supplier` value can't be resolved |

### `VendorBillsImport`

| Field | Behavior |
| --- | --- |
| Required | `supplier` |
| Optional | `bill_number`, `vendor_reference`, `bill_date`, `due_date`, `status`, `subtotal`, `tax`, `total`, `notes` |
| Upsert key | `bill_number` (exact match), only when provided |
| Foreign keys | `supplier` → `suppliers.name` |
| Skip when | `supplier` value can't be resolved |

### `VendorBillPaymentsImport`

| Field | Behavior |
| --- | --- |
| Required | `bill_number`, `amount` |
| Optional | `payment_date`, `payment_method`, `reference`, `notes` |
| Upsert key | `bill_number` + `reference` (rejects duplicate payments at the same reference) |
| Foreign keys | `bill_number` → `vendor_bills.bill_number` |
| Side-effects | Auto-flips the bill status to `paid` or `partial` based on total amount paid |
| Skip when | Bill not found, payment can't be registered, or duplicate `reference` exists |

---

## Inventory

### `ProductsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `sku`, `category` (auto-created if missing), `type`, `cost_price`, `selling_price`, `quantity`, `min_stock`, `description`, `status` |
| Upsert key | `sku` (exact match), only when provided |
| Foreign keys | `category` → `categories.name`, **auto-created** if not found |
| Skip when empty | `name` |

### `CategoriesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code`, `description`, `parent` (category name), `color`, `is_active`, `sort_order` |
| Upsert key | `code` if provided, else `name` (case-insensitive) |
| Foreign keys | `parent` → `categories.name` |
| Skip when empty | `name` |

### `WarehousesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `location`, `contact_info` |
| Upsert key | `name` (case-insensitive) |
| Foreign keys | — |
| Skip when empty | `name` |

### `InventoryAdjustmentsImport`

| Field | Behavior |
| --- | --- |
| Required | `warehouse`, `product` or `sku`, `quantity` |
| Optional | `date`, `type` (`increase` / `decrease` / `count`), `reason`, `auto_post` |
| Upsert key | insert-only (no update path) |
| Foreign keys | `warehouse` → `warehouses.name`; `product` → `products.sku` or `products.name` |
| Side-effects | If `auto_post=true`, posts the stock movement immediately on import |
| Skip when | warehouse or product can't be resolved |

### `InventoryTransfersImport`

| Field | Behavior |
| --- | --- |
| Required | `source_warehouse`, `destination_warehouse`, `product` or `sku`, `quantity` |
| Optional | `transfer_number` (groups multiple line rows into one transfer), `date`, `expected_arrival`, `notes` |
| Upsert key | `transfer_number` groups rows. Values starting with `new_` always create new transfers |
| Foreign keys | `source_warehouse`, `destination_warehouse` → `warehouses.name`; `product` → `products.sku` or `products.name` |
| Skip when | warehouse or product can't be resolved |

---

## Delivery

### `DeliveryOrdersImport`

| Field | Behavior |
| --- | --- |
| Required | — (insert-only; `delivery_number` auto-generates) |
| Optional | `delivery_number`, `sales_order`, `warehouse`, `delivery_date`, `actual_delivery_date`, `status` (must match a `DeliveryOrderState`), `shipping_address`, `recipient_name`, `recipient_phone`, `tracking_number`, `courier`, `notes` |
| Upsert key | `delivery_number` (exact match), only when provided |
| Foreign keys | `sales_order` → `sales_orders.order_number`; `warehouse` → `warehouses.name` |
| Skip when | `sales_order` value provided but can't be resolved |

---

## Invoicing

### `InvoicesImport`

| Field | Behavior |
| --- | --- |
| Required | `customer` |
| Optional | `invoice_number`, `invoice_date` (default today), `due_date` (default today + 30 days), `status`, `subtotal`, `tax`, `discount`, `total`, `notes`, `terms` |
| Upsert key | `invoice_number` (exact match), only when provided |
| Foreign keys | `customer` → `customers.name` |
| Skip when | `customer` value can't be resolved |

### `PaymentsImport`

| Field | Behavior |
| --- | --- |
| Required | `invoice_number`, `amount` |
| Optional | `payment_method`, `reference`, `notes` |
| Upsert key | `invoice_number` + `reference` (rejects duplicate payments at the same reference) |
| Foreign keys | `invoice_number` → `invoices.invoice_number` |
| Side-effects | Auto-flips the invoice status to `paid` or `partial` based on total amount paid |
| Skip when | Invoice not found, payment can't be registered, or duplicate `reference` exists |

---

## Accounting

### `AccountsImport`

| Field | Behavior |
| --- | --- |
| Required | `code`, `name` |
| Optional | `type` (default `asset`), `parent_code`, `description`, `is_active`, `is_system` |
| Upsert key | `code` (exact match) |
| Foreign keys | `parent_code` → `accounts.code` |
| Skip when empty | `name` or `code` |

### `JournalEntriesImport`

| Field | Behavior |
| --- | --- |
| Required | `account_code` or `account`; `debit` or `credit` (at least one per line) |
| Optional | `entry_number` (groups multi-line entries), `date`, `reference`, `description`, `line_description`, `auto_post` |
| Upsert key | `entry_number` groups rows. Values starting with `new_` always create new entries |
| Foreign keys | `account_code` → `accounts.code`; `account` → `accounts.name` |
| Validation | Total debits must equal total credits within each `entry_number` |
| Skip when | Account not found, or debit/credit totals don't balance for an entry |

---

## CRM

### `LeadsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `email`, `phone`, `company_name`, `job_title`, `website`, `address`, `source`, `status`, `notes`, `assigned_to` |
| Upsert key | `email` (exact match) |
| Foreign keys | `assigned_to` → `users.name` or `users.email` |
| Skip when empty | `name` |

### `OpportunitiesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `customer`, `stage`, `assigned_to`, `expected_revenue`, `probability` (default 50), `expected_close_date`, `description` |
| Upsert key | `name` + `customer_id` (case-insensitive on `name`) |
| Foreign keys | `customer` → `customers.name`; `stage` → `pipelines.name`; `assigned_to` → `users.name` |
| Skip when empty | `name` |

---

## HR

### `DepartmentsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name's first 3 chars uppercased if missing), `description`, `is_active` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | — |
| Skip when empty | `name` |

### `PositionsImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name), `department`, `description`, `is_active` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | `department` → `departments.name` |
| Skip when empty | `name` |

### `EmployeesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `email`, `phone`, `mobile`, `department`, `position`, `hire_date`, `employment_type`, `status`, `basic_salary`, `bank_name`, `bank_account_number`, `bank_account_name`, `address`, `city` |
| Upsert key | `email` (exact match) |
| Foreign keys | `department` → `departments.name`; `position` → `positions.name` |
| Skip when empty | `name` |

### `LeaveTypesImport`

| Field | Behavior |
| --- | --- |
| Required | `name` |
| Optional | `code` (auto-generated from name), `days_per_year`, `is_paid`, `requires_approval`, `is_active`, `description` |
| Upsert key | `code` (exact match), only when provided |
| Foreign keys | — |
| Skip when empty | `name` |

---

## Troubleshooting

**"Row N: The name field is required."**
A row's `name` column is blank or whitespace-only. Many importers
silently skip these — if you see this message in the error CSV, the
importer also has a stricter rule that promoted the blank to an error
(usually because `name` is the primary key column, not a fallback).

**"Row N: customer/supplier/product not found."**
The foreign-key column couldn't be resolved against the lookup table.
Check the exact spelling (most lookups are case-sensitive). For names
that vary by capitalization, fix the source spreadsheet to match the
canonical record name.

**Two rows produce the same record.**
Upsert keys are evaluated per-row. If two rows share the same `email`
(or whatever the upsert key is) the second row's values overwrite the
first — the import shows 2 *updated*, not 2 *imported*. Deduplicate
in the source spreadsheet before upload.

**Importer aborts before any row is imported.**
Shouldn't happen any more — every shipped importer implements
`SkipsOnFailure` since commit `570e75a`. If you see this, the importer
threw a non-validation exception (file format, permission, missing
column header). Check the error in the modal — it'll have row 0 and a
single message.

**Bulk-update an existing record set.**
Export the current data (most index pages have an *Export* action),
edit it in Excel, re-upload. The upsert key (usually `code`, `sku`,
or `email`) matches the exported rows back to their records and
updates them. The export-edit-import cycle is the canonical way to
bulk-edit Syncore data.
