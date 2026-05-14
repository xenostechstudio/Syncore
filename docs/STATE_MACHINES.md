# State Machines

Every transactional document in Syncore (sales order, invoice, delivery,
purchase, payment, transfer, lead, leave request, payroll, etc.) is a
state machine driven by a backed PHP enum + the `HasStateMachine` trait.

This page is the canonical reference for every state and every
transition. If you're trying to answer "what happens when I click
'Confirm' on a sales order?" or "why is this invoice stuck in `sent`?",
start here.

## How it works (in one paragraph)

A model that uses `HasStateMachine` (e.g. `Invoice`) declares
`protected string $stateEnum = InvoiceState::class;`. The DB column is a
plain string (`status`), but the model exposes a `$model->state`
accessor that returns the enum instance — so views and code can call
`$invoice->state->label()`, `$invoice->state->isTerminal()`, etc. The
trait also provides `transitionTo(NewState)`, which writes the new
string back to `status` and fires the standard model events. Domain
methods on the model (`Invoice::send()`, `SalesOrder::confirm()`, …)
wrap `transitionTo` with the business guards — they refuse to fire if
the transition isn't allowed.

Render the badge with `<x-ui.status-badge :status="$model->state">` —
the component reads `label()` + `color()` off the enum. See
[`docs/ARCHITECTURE.md`](ARCHITECTURE.md#status-display-enum-driven)
for the matched Tailwind palette caveat.

---

## Sales — `SalesOrderState`

Document: a sales order or quotation. Source:
[`app/Models/Sales/SalesOrder.php`](../app/Models/Sales/SalesOrder.php),
[`app/Enums/SalesOrderState.php`](../app/Enums/SalesOrderState.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `QUOTATION` | `draft` | no |
| `QUOTATION_SENT` | `confirmed` | no |
| `SALES_ORDER` | `processing` | no |
| `DONE` | `delivered` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
QUOTATION ──sendQuotation()──→ QUOTATION_SENT
    │                                │
    └─────────confirm()──────────────┴──→ SALES_ORDER
                                                │
                                                ├──lock()─────→ DONE
                                                │
                                                └──cancelOrder()─→ CANCELLED
```

Triggered by:
- `QUOTATION → QUOTATION_SENT`: `SalesOrder::sendQuotation()` (email + status flip)
- `QUOTATION / QUOTATION_SENT → SALES_ORDER`: `SalesOrder::confirm()` — exposed from the SO form's *Confirm* button. Refuses if `stock_check_mode` setting blocks it and stock is short.
- `SALES_ORDER → DONE`: **automatic** via `SalesOrderFulfillmentService::maybeLockOnFullFulfillment()`. Every time an Invoice/DeliveryOrder observer recomputes the SO's fulfillment counters, the service checks whether the SO is now fully **paid** *and* fully **delivered** — if so, it calls `lock()` to flip to DONE. "Fully paid" (`SalesOrder::isFullyPaid()`) means every ordered line is invoiced *and* every non-cancelled invoice has settled to `paid` — money in, goods out. Settling the last invoice (manual payment, `markAsPaid()`, or a Xendit `PAID` webhook) is what fires the `InvoiceObserver` recompute that triggers the lock. One-way: cancelling an invoice or delivery *after* lock won't reverse the DONE state (the counters will reflect the cancellation but the SO stays terminal — by design).
- `→ CANCELLED`: `SalesOrder::cancelOrder()` — refuses if non-cancelled invoices or deliveries exist.

Side-effects: the `quantity_invoiced` / `quantity_delivered` counters
on `SalesOrderItem` are kept in sync by observers on `InvoiceItem`,
`Invoice`, `DeliveryOrderItem`, `DeliveryOrder`. See
[`app/Services/SalesOrderFulfillmentService.php`](../app/Services/SalesOrderFulfillmentService.php).

---

## Invoicing — `InvoiceState`

Document: a customer invoice. Source:
[`app/Models/Invoicing/Invoice.php`](../app/Models/Invoicing/Invoice.php),
[`app/Enums/InvoiceState.php`](../app/Enums/InvoiceState.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `SENT` | `sent` | no |
| `PARTIAL` | `partial` | no |
| `OVERDUE` | `overdue` | no |
| `PAID` | `paid` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──send()──→ SENT ◀──┐
  │                 │     │
  │                 ├─markAsPartial()──→ PARTIAL ──┐
  │                 │                              │
  │                 ├─markAsOverdue()──→ OVERDUE ──┤
  │                 │                              │
  │                 └─markAsPaid()─→ PAID          │
  │                                                │
  └──cancelInvoice()─────────────────────────────→ CANCELLED
                       (from DRAFT / SENT only)
```

Triggered by:
- `DRAFT → SENT`: `Invoice::send()` — manual *Send* action or the Xendit webhook on successful payment-link generation.
- `SENT / PARTIAL / OVERDUE → PARTIAL`: `InvoiceService::registerPayment()` when payment < total.
- `SENT / PARTIAL / OVERDUE → PAID`: `InvoiceService::registerPayment()` when payment ≥ total. Also fires from the Xendit webhook for PAID events.
- `SENT / PARTIAL → OVERDUE`: `Invoice::markAsOverdue()` — currently a manual action; scheduling is not wired (yet).
- `→ CANCELLED`: `InvoiceService::cancel()`. The `InvoiceObserver` recomputes the parent SO's `quantity_invoiced` (cancelled invoice items don't count).

---

## Delivery — `DeliveryOrderState`

Document: a delivery order (DO). Source:
[`app/Models/Delivery/DeliveryOrder.php`](../app/Models/Delivery/DeliveryOrder.php),
[`app/Enums/DeliveryOrderState.php`](../app/Enums/DeliveryOrderState.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `PENDING` | `pending` | no |
| `PICKED` | `picked` | no |
| `IN_TRANSIT` | `in_transit` | no |
| `DELIVERED` | `delivered` | **yes** |
| `FAILED` | `failed` | **yes** |
| `RETURNED` | `returned` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
PENDING ──markAsPicked()──→ PICKED ──markInTransit()──→ IN_TRANSIT
   │                          │                              │
   │                          │                              ├─markAsDelivered()─→ DELIVERED
   │                          │                              │
   │                          │                              └─markAsFailed(reason)─→ FAILED
   │                          │
   └────cancelDelivery()──────┘
   (from PENDING or PICKED)
              ↓
         CANCELLED
```

Triggered by:
- The chain `PENDING → PICKED → IN_TRANSIT → DELIVERED` is the *Update Status* button on the DO form (`Livewire/Delivery/Orders/Form::confirmStatusTransition`). It refuses to advance to `DELIVERED` if warehouse stock is short.
- On reaching `DELIVERED`: a WH/OUT `InventoryAdjustment` is posted (see [`Services/InventoryService`](../app/Services/InventoryService.php)), the `DeliveryCompleted` event fires (notifications + analytics), and the parent SO's `quantity_delivered` counters recompute via `DeliveryOrderObserver`.
- `→ FAILED`: `DeliveryOrder::markAsFailed(reason)` — increments `delivery_attempts`, records the reason.
- `→ RETURNED`: not transitioned to directly — created when a `DeliveryReturn` is received and posted (separate state machine on `DeliveryReturn`).
- `→ CANCELLED`: `Livewire/Delivery/Orders/Form::cancelOrder()` — only from `PENDING` or `PICKED`.

POD + customer feedback are captured *after* a DO is `DELIVERED` via
`recordProofOfDelivery()` / `recordCustomerFeedback()`. The buttons hide
once the data is captured (see commit `2233116`).

---

## Purchase — `PurchaseOrderState`

Document: a purchase RFQ that progresses through to a billed PO.
Source:
[`app/Models/Purchase/PurchaseRfq.php`](../app/Models/Purchase/PurchaseRfq.php),
[`app/Enums/PurchaseOrderState.php`](../app/Enums/PurchaseOrderState.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `RFQ` | `rfq` | no |
| `RFQ_SENT` | `sent` | no |
| `PURCHASE_ORDER` | `purchase_order` | no |
| `PARTIALLY_RECEIVED` | `partially_received` | no |
| `RECEIVED` | `received` | no |
| `BILLED` | `billed` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
RFQ ──sendRfq()──→ RFQ_SENT ──confirmOrder()──→ PURCHASE_ORDER
                                                       │
                                                       ├─markAsPartiallyReceived()─→ PARTIALLY_RECEIVED
                                                       │                                    │
                                                       └─markAsReceived()─→ RECEIVED ◀──────┘
                                                                               │
                                                                               └─markAsBilled()─→ BILLED
            cancelOrder() from any of RFQ / RFQ_SENT / PURCHASE_ORDER → CANCELLED
```

Triggered by:
- `RFQ → RFQ_SENT`: `PurchaseRfq::sendRfq()` — fires an email to the supplier when `auto_send_to_supplier` setting is on; otherwise just flips status. See `Services/PurchaseService`.
- `RFQ → PURCHASE_ORDER`: `confirmOrder()` — refuses when the PO total ≥ `approval_threshold` setting and the user lacks approval permission.
- `PURCHASE_ORDER → PARTIALLY_RECEIVED / RECEIVED`: the goods-receipt flow (`PurchaseReceipt::validate()`) flips the parent PO automatically based on whether every line is fully received.
- `RECEIVED → BILLED`: created via `VendorBillService::createFromPurchaseOrder()`.

---

## Purchase — `PurchaseReceiptState`

Document: a goods-receipt note against a PO. Source:
[`app/Models/Purchase/PurchaseReceipt.php`](../app/Models/Purchase/PurchaseReceipt.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `VALIDATED` | `validated` | no |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──validate()──→ VALIDATED
  │                       │
  └──cancel()─────────────┴──→ CANCELLED
```

Triggered by `PurchaseReceipt::validate()` (the *Validate Receipt*
button). On validate, a WH/IN `InventoryAdjustment` posts and the parent
PO's `quantity_received` is updated.

---

## Purchase — `VendorBillState`

Document: a vendor bill (the supplier-side analogue of `Invoice`).
Source: [`app/Models/Procurement/VendorBill.php`](../app/Models/Procurement/VendorBill.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `PENDING` | `pending` | no |
| `PARTIAL` | `partial` | no |
| `OVERDUE` | `overdue` | no |
| `PAID` | `paid` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

Same shape as `InvoiceState` but on the AP side. Transitions:
- `DRAFT → PENDING` (`confirm()`)
- `PENDING / PARTIAL / OVERDUE → PARTIAL / PAID / OVERDUE` (via payment registration)
- `DRAFT / PENDING → CANCELLED` (`cancelBill()`)

---

## Invoicing — `PaymentState`

Document: a payment record attached to an `Invoice`. Source:
[`app/Models/Invoicing/Payment.php`](../app/Models/Invoicing/Payment.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `PENDING` | `pending` | no |
| `PROCESSING` | `processing` | no |
| `COMPLETED` | `completed` | **yes** |
| `FAILED` | `failed` | no |
| `CANCELLED` | `cancelled` | **yes** |

```
PENDING ──→ PROCESSING ──→ COMPLETED
  │            │
  │            └─→ FAILED ─→ PENDING (retry)
  │
  └──→ CANCELLED
```

Most manual payments are recorded directly as `COMPLETED` via
`InvoiceService::registerPayment()`. Xendit-initiated payments live in
`PROCESSING` until the webhook confirms and flips them to `COMPLETED`.

---

## Inventory — `TransferState`

Document: an inter-warehouse transfer. Source:
[`app/Models/Inventory/InventoryTransfer.php`](../app/Models/Inventory/InventoryTransfer.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `READY` | `ready` | no |
| `IN_TRANSIT` | `in_transit` | no |
| `COMPLETED` | `completed` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──markReady()──→ READY ──markInTransit()──→ IN_TRANSIT ──complete()──→ COMPLETED
  │                      │
  └──cancelTransfer()────┴──→ CANCELLED
```

`markReady()` posts a WH/OUT against the source warehouse; `complete()`
posts a WH/IN against the destination. The pairing is what makes the
transfer count toward both warehouses' stock ledgers.

---

## Inventory — `AdjustmentState`

Document: a manual inventory adjustment (e.g. cycle count correction).
Source: [`app/Models/Inventory/InventoryAdjustment.php`](../app/Models/Inventory/InventoryAdjustment.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `COMPLETED` | `completed` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──post()──→ COMPLETED
  │
  └──cancelAdjustment()──→ CANCELLED
```

`post()` writes the stock delta to `InventoryStock` and sets `posted_at`.

System-generated adjustments (WH/OUT from delivery, WH/IN from receipt)
start in `COMPLETED` directly — they bypass `DRAFT` because the source
document already validated the movement. Look for
`source_delivery_order_id` / `source_purchase_receipt_id` to tell user-
posted from system-posted adjustments.

---

## HR — `LeaveRequestState`

Document: an employee's leave request. Source:
[`app/Models/HR/LeaveRequest.php`](../app/Models/HR/LeaveRequest.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `PENDING` | `pending` | no |
| `APPROVED` | `approved` | **yes** |
| `REJECTED` | `rejected` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──submit()──→ PENDING ──approve()──→ APPROVED
  │                   │
  │                   └─reject(reason)──→ REJECTED
  │
  └──cancel()──→ CANCELLED
```

- `submit()`: employee files the request (DRAFT → PENDING).
- `approve()` / `reject()`: manager action; both terminal.
- `cancel()` is restricted to the employee themselves, and only from
  `DRAFT` or `PENDING` (you can't cancel an approved leave — talk to
  HR instead).

---

## HR — `PayrollState`

Document: a payroll run / employee payroll item.

| State | DB value | Terminal? |
| --- | --- | --- |
| `DRAFT` | `draft` | no |
| `APPROVED` | `approved` | no |
| `PROCESSING` | `processing` | no |
| `PAID` | `paid` | **yes** |
| `CANCELLED` | `cancelled` | **yes** |

```
DRAFT ──approve()──→ APPROVED ──startProcessing()──→ PROCESSING ──markPaid()──→ PAID
  │                     │
  │                     ├─resetToDraft()──→ DRAFT (back-edit before processing)
  │                     │
  └────────cancel()─────┴──→ CANCELLED
```

Once `PROCESSING` is entered, the only valid forward transition is
`PAID`. There is no rollback from `PROCESSING` — refund / correction is
out-of-band.

---

## HR — `EmployeeStatus`

Not a transactional state machine (no `transitionTo` guards), just a
status enum.

| State | DB value | Notes |
| --- | --- | --- |
| `ACTIVE` | `active` | normal |
| `ON_LEAVE` | `on_leave` | currently on approved leave |
| `INACTIVE` | `inactive` | terminated / archived |

`ACTIVE ↔ ON_LEAVE` happens automatically via the leave-request
approval flow. `INACTIVE` is set by HR's *Terminate* action; reversing
it is a manual override.

---

## CRM — `LeadState`

Document: a sales lead. Source: [`app/Models/CRM/Lead.php`](../app/Models/CRM/Lead.php).

| State | DB value | Terminal? |
| --- | --- | --- |
| `NEW` | `new` | no |
| `CONTACTED` | `contacted` | no |
| `QUALIFIED` | `qualified` | no |
| `CONVERTED` | `converted` | **yes** |
| `LOST` | `lost` | **yes** |

```
NEW ──markAsContacted()──→ CONTACTED ──markAsQualified()──→ QUALIFIED
                                                                 │
                                                                 └─convertToCustomer()─→ CONVERTED
              markAsLost(reason) from any non-terminal      ─→ LOST
```

`convertToCustomer()` materialises a `Customer` from the lead's data
and (optionally) an `Opportunity`. Both are kept linked back to the
original `Lead` for audit.

---

## CRM — `OpportunityState`

Document: a sales opportunity (often spawned from a lead).

| State | DB value | Terminal? |
| --- | --- | --- |
| `OPEN` | `open` | no |
| `WON` | `won` | **yes** |
| `LOST` | `lost` | **yes** |

```
OPEN ──markAsWon(salesOrderId)──→ WON
  │
  └─markAsLost(reason)──→ LOST
```

`markAsWon` requires a `sales_order_id` — the link gives the dashboard
won-revenue figure something concrete to total against.

---

## Conventions notes

- **All state writes go through `transitionTo`** (or a domain method that
  wraps it). Never `UPDATE status =` directly — driver-aware enum CHECK
  constraints in Postgres will reject unknown values, and you'll
  bypass `LogsActivity`.

- **Driver caveat**: SQLite ignores `enum()` CHECK constraints, so test
  fixtures can pass strings that Postgres will reject. When you add or
  remove a state value, write a [driver-aware
  migration](../CLAUDE.md#driver-aware-status-enum-migrations) — see
  `2026_04_25_120000_simplify_employee_status_enum` for a canonical
  example.

- **Terminal states reject further transitions.** If `cancelInvoice()`
  is called on a `PAID` invoice, the method returns `false` and the
  state is unchanged. UI should hide the action button, not just rely
  on the guard.

- **Side-effects on transition**: anything that changes shared state
  (inventory, fulfillment counters, notifications) is a model observer
  or service-method call wrapped around the `transitionTo`. See
  [`app/Services/SalesOrderFulfillmentService.php`](../app/Services/SalesOrderFulfillmentService.php)
  for the fulfillment-counter pattern and
  [`app/Services/InventoryService.php`](../app/Services/InventoryService.php)
  for the warehouse-adjustment pattern.
