# Application Guide

Comprehensive guide for using the ERP system modules.

## Table of Contents

1. [Dashboard](#dashboard)
2. [Sales Module](#sales-module)
3. [Invoicing Module](#invoicing-module)
4. [Inventory Module](#inventory-module)
5. [Delivery Module](#delivery-module)
6. [Purchase Module](#purchase-module)
7. [CRM Module](#crm-module)
8. [HR Module](#hr-module)
9. [Accounting Module](#accounting-module)
10. [Reports Module](#reports-module)
11. [Settings](#settings)

---

## Dashboard

The main dashboard provides a real-time overview of your business operations.

### Key Metrics
- **Sales**: Total sales, order count, average order value with month-over-month comparison
- **Invoices**: Outstanding amount, overdue invoices, paid this month
- **Inventory**: Total products, low stock alerts, out of stock items, inventory value
- **Purchases**: Pending bills, overdue bills, paid this month

### Widgets
- Sales chart (6-month trend)
- Top customers by revenue
- Top products by sales
- Low stock alerts
- Recent activities
- Pending actions requiring attention

### Quick Actions
- Create new sales order
- Create new invoice
- View reports
- Access settings

---

## Sales Module

Manage your sales pipeline from quotation to order fulfillment.

### Customers

**Creating a Customer:**
1. Navigate to Sales → Customers
2. Click "New Customer"
3. Fill in required fields:
   - Name
   - Email (optional)
   - Phone (optional)
   - Address details
4. Save

**Customer Features:**
- Contact information management
- Sales history tracking
- Credit limit settings
- Tax ID for invoicing

### Sales Orders

**Order Workflow:**
```
Draft → Quotation → Confirmed → Sales Order → Invoiced/Delivered
```

**Creating a Sales Order:**
1. Navigate to Sales → Orders
2. Click "New Order"
3. Select customer
4. Add products with quantities and prices
5. Apply discounts if needed
6. Save as draft or send quotation

**Order Actions:**
- **Confirm**: Convert quotation to confirmed order
- **Create Invoice**: Generate invoice from order
- **Create Delivery**: Schedule delivery for order items
- **Cancel**: Cancel the order (if not invoiced/delivered)

### Pricelists

Configure different pricing strategies:
- Customer-specific pricing
- Volume discounts
- Time-limited promotions
- Currency-specific prices

---

## Invoicing Module

Handle all billing and payment operations.

### Invoices

**Invoice Workflow:**
```
Draft → Sent → Partial/Paid → Overdue (if past due date)
```

**Creating an Invoice:**
1. Navigate to Invoicing → Invoices
2. Click "New Invoice" or create from Sales Order
3. Verify customer and line items
4. Set payment terms and due date
5. Send to customer

**Invoice Features:**
- PDF generation
- Email sending
- Payment tracking
- Partial payments support
- Overdue notifications

### Payments

**Recording a Payment:**
1. Open the invoice
2. Click "Record Payment"
3. Enter payment details:
   - Amount
   - Payment method (Bank Transfer, Cash, Credit Card, etc.)
   - Payment date
   - Reference number
4. Save

**Payment Methods:**
- Bank Transfer
- Cash
- Credit Card
- Online Payment (Xendit integration)

### Credit Notes

Issue credit notes for:
- Returns
- Pricing adjustments
- Service credits

---

## Inventory Module

Manage products, stock levels, and warehouse operations.

### Products

**Product Information:**
- SKU (unique identifier)
- Name and description
- Category
- Cost price and selling price
- Unit of measure
- Stock quantity
- Minimum stock level (for alerts)

**Creating a Product:**
1. Navigate to Inventory → Products
2. Click "New Product"
3. Fill in product details
4. Set pricing information
5. Configure stock settings
6. Save

### Categories

Organize products into categories for:
- Better navigation
- Reporting
- Pricing rules

### Warehouses

**Multi-Warehouse Support:**
- Track stock by location
- Transfer between warehouses
- Set default warehouse

### Stock Operations

**Stock Adjustments:**
- Increase/decrease stock manually
- Record reasons for adjustments
- Audit trail for all changes

**Stock Transfers:**
```
Draft → In Transit → Completed
```

1. Create transfer request
2. Specify source and destination warehouses
3. Select products and quantities
4. Process transfer

### Inventory Reports
- Stock valuation
- Low stock report
- Stock movement history
- Warehouse comparison

---

## Delivery Module

Manage order fulfillment and shipping.

### Delivery Orders

**Delivery Workflow:**
```
Draft → Ready → Shipped → Delivered
```

**Creating a Delivery:**
1. Create from Sales Order or manually
2. Select items to deliver
3. Set shipping details
4. Process delivery

**Delivery Features:**
- Partial deliveries
- Delivery tracking
- Proof of delivery
- Return handling

### Delivery States
- **Draft**: Being prepared
- **Ready**: Ready for pickup/shipping
- **Shipped**: In transit
- **Delivered**: Successfully delivered
- **Cancelled**: Delivery cancelled

---

## Purchase Module

Manage vendor relationships and procurement.

### Suppliers

**Supplier Information:**
- Company details
- Contact information
- Payment terms
- Tax information

### Purchase Orders

**Purchase Workflow:**
```
RFQ → RFQ Sent → Purchase Order → Received → Billed
```

**Creating a Purchase Order:**
1. Navigate to Purchase → Orders
2. Click "New RFQ"
3. Select supplier
4. Add products with quantities
5. Send RFQ or confirm as PO

### Vendor Bills

**Bill Workflow:**
```
Draft → Pending → Partial/Paid → Overdue
```

**Recording a Vendor Bill:**
1. Create from Purchase Order or manually
2. Enter bill details
3. Verify amounts
4. Schedule payment

### Bill Payments

Track payments to vendors:
- Payment scheduling
- Partial payments
- Payment history

---

## CRM Module

Manage leads, opportunities, and customer relationships.

### Leads

**Lead Sources:**
- Website
- Referral
- Social Media
- Trade Show
- Cold Call
- Other

**Lead Workflow:**
```
New → Contacted → Qualified → Converted/Lost
```

**Managing Leads:**
1. Create lead with contact information
2. Assign to sales representative
3. Track interactions
4. Convert to customer when qualified

### Opportunities

**Pipeline Stages:**
Customizable stages with probability percentages:
- Qualification (10%)
- Needs Analysis (25%)
- Proposal (50%)
- Negotiation (75%)
- Closed Won (100%)
- Closed Lost (0%)

**Opportunity Features:**
- Expected revenue tracking
- Close date forecasting
- Activity logging
- Win/loss analysis

### Activities

Track all customer interactions:
- Calls
- Meetings
- Emails
- Tasks
- Notes

---

## HR Module

Manage employees, attendance, leave, and payroll.

### Employees

**Employee Information:**
- Personal details
- Employment information
- Department assignment
- Salary details
- Emergency contacts

### Departments

Organize employees by department:
- Department hierarchy
- Manager assignment
- Budget tracking

### Attendance

**Attendance Tracking:**
- Check-in/check-out
- Work hours calculation
- Overtime tracking
- Attendance reports

### Leave Management

**Leave Types:**
- Annual Leave
- Sick Leave
- Personal Leave
- Maternity/Paternity Leave
- Custom types

**Leave Request Workflow:**
```
Draft → Pending → Approved/Rejected
```

**Requesting Leave:**
1. Employee submits request
2. Manager receives notification
3. Manager approves/rejects
4. Leave balance updated automatically

### Payroll

**Payroll Workflow:**
```
Draft → Approved → Processing → Paid
```

**Processing Payroll:**
1. Create payroll period
2. Generate payroll items for employees
3. Review and adjust if needed
4. Approve payroll
5. Process payments
6. Mark as paid

**Salary Components:**
- Basic salary
- Allowances (transport, meal, etc.)
- Deductions (tax, insurance, etc.)
- Overtime pay
- Bonuses

---

## Accounting Module

Financial management and reporting.

### Chart of Accounts

Standard account structure:
- Assets
- Liabilities
- Equity
- Revenue
- Expenses

### Journal Entries

Record financial transactions:
- Manual entries
- Auto-generated from invoices/bills
- Adjusting entries

### Financial Reports
- Balance Sheet
- Income Statement
- Cash Flow Statement
- Trial Balance

---

## Reports Module

Comprehensive reporting across all modules.

### Sales Reports
- Sales by period (daily/weekly/monthly/yearly)
- Sales by customer
- Sales by product
- Salesperson performance

### Inventory Reports
- Stock valuation
- Low stock items
- Stock by warehouse
- Movement history

### Financial Reports
- Revenue by period
- Aging report (receivables)
- Payment methods analysis
- Collection rate

### HR Reports
- Employee by department
- Turnover rate
- Leave analysis
- Payroll summary
- Attendance summary

### CRM Reports
- Lead conversion funnel
- Pipeline analysis
- Win/loss analysis
- Sales forecast
- Activity metrics

### Purchase Reports
- Purchases by period
- Purchases by supplier
- Bill aging report
- Supplier performance

### Export Options
- Excel (.xlsx)
- CSV
- PDF

---

## Settings

Configure system-wide settings.

### Company Settings
- Company information
- Logo
- Address
- Tax settings

### Localization
- Language
- Timezone
- Date format
- Currency settings
- Number format

### Users & Roles

**User Management:**
- Create/edit users
- Assign roles
- Set permissions

**Default Roles:**
- Super Admin: Full access
- Admin: Most features except critical settings
- Manager: Department-level access
- User: Basic access

### Email Configuration
- SMTP settings
- Email templates
- Notification preferences

### Payment Gateway
- Xendit integration
- API keys configuration
- Webhook setup

### Audit Trail
- View all system activities
- Filter by user/action/date
- Export audit logs

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + S` | Save current form |
| `Ctrl + N` | New record |
| `Esc` | Close modal/cancel |
| `/` | Focus search |

---

## Tips & Best Practices

1. **Regular Backups**: Ensure database backups are scheduled
2. **Stock Alerts**: Set appropriate minimum stock levels
3. **Invoice Promptly**: Create invoices immediately after delivery
4. **Follow Up**: Use CRM activities to track customer follow-ups
5. **Reconcile**: Regularly reconcile payments with bank statements
6. **Review Reports**: Check dashboard and reports daily
7. **Audit Trail**: Review audit logs for unusual activities
