# API Reference

REST API documentation for external integrations.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

All API endpoints (except health check) require authentication using Laravel Sanctum.

### Getting a Token

```bash
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "your-password"
}
```

### Using the Token

Include the token in the Authorization header:

```bash
Authorization: Bearer your-api-token
```

---

## Rate Limiting

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Standard API | 60 requests | 1 minute |
| Heavy operations | 10 requests | 1 minute |
| Export endpoints | 5 requests | 5 minutes |

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests

---

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

---

## Endpoints

### Health Check

#### GET /api/health

Public endpoint to check system health.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2026-01-11T10:00:00+00:00",
  "php_version": "8.4.13",
  "laravel_version": "12.41.1",
  "memory": {
    "usage_mb": 52.5,
    "peak_mb": 54.5,
    "limit": "128M"
  },
  "database": {
    "status": "connected",
    "response_time_ms": 1.66
  },
  "cache": {
    "status": "connected",
    "driver": "database",
    "response_time_ms": 12.61
  },
  "queue": {
    "status": "configured",
    "driver": "database"
  }
}
```

#### GET /api/v1/health/detailed

Detailed health metrics (requires authentication).

---

### Dashboard

#### GET /api/v1/dashboard

Get all dashboard data.

**Query Parameters:**
- `fresh` (boolean): Skip cache and get fresh data

**Response:**
```json
{
  "success": true,
  "data": {
    "sales": { ... },
    "invoices": { ... },
    "inventory": { ... },
    "purchases": { ... },
    "pending_actions": { ... },
    "cash_flow": { ... },
    "top_customers": [ ... ],
    "top_products": [ ... ],
    "sales_chart": [ ... ],
    "low_stock": [ ... ],
    "recent_activities": [ ... ],
    "recent_orders": [ ... ],
    "recent_invoices": [ ... ]
  }
}
```

#### GET /api/v1/dashboard/kpi

Get key performance indicators.

#### GET /api/v1/dashboard/sales

Get sales widget data.

#### GET /api/v1/dashboard/inventory

Get inventory widget data.

#### GET /api/v1/dashboard/invoicing

Get invoicing widget data.

#### GET /api/v1/dashboard/hr

Get HR widget data.

#### GET /api/v1/dashboard/crm

Get CRM widget data.

#### GET /api/v1/dashboard/purchase

Get purchase widget data.

---

### Customers

#### GET /api/v1/customers

List all customers.

**Query Parameters:**
- `search` (string): Search by name, email, or phone
- `status` (string): Filter by status
- `per_page` (integer): Items per page (default: 15)
- `page` (integer): Page number

**Example:**
```bash
GET /api/v1/customers?search=john&per_page=10
```

#### GET /api/v1/customers/{id}

Get a specific customer with recent orders.

#### POST /api/v1/customers

Create a new customer.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+62812345678",
  "address": "123 Main St",
  "city": "Jakarta",
  "country": "Indonesia",
  "tax_id": "12.345.678.9-012.000"
}
```

#### PUT /api/v1/customers/{id}

Update a customer.

#### DELETE /api/v1/customers/{id}

Delete a customer (fails if customer has orders).

---

### Products

#### GET /api/v1/products

List all products.

**Query Parameters:**
- `search` (string): Search by name or SKU
- `category_id` (integer): Filter by category
- `status` (string): Filter by status (active/inactive)
- `low_stock` (boolean): Show only low stock items
- `per_page` (integer): Items per page

#### GET /api/v1/products/{id}

Get a specific product with stock details.

#### GET /api/v1/products/{id}/stock

Get stock levels by warehouse.

**Response:**
```json
{
  "success": true,
  "data": {
    "product_id": 1,
    "total_quantity": 150,
    "stocks": [
      {
        "warehouse_id": 1,
        "warehouse_name": "Main Warehouse",
        "quantity": 100
      },
      {
        "warehouse_id": 2,
        "warehouse_name": "Secondary",
        "quantity": 50
      }
    ]
  }
}
```

#### POST /api/v1/products

Create a new product.

**Request Body:**
```json
{
  "name": "Product Name",
  "sku": "PRD-001",
  "category_id": 1,
  "description": "Product description",
  "cost_price": 10000,
  "selling_price": 15000,
  "quantity": 100,
  "unit": "pcs",
  "status": "active"
}
```

#### PUT /api/v1/products/{id}

Update a product.

#### DELETE /api/v1/products/{id}

Delete a product.

---

### Invoices

#### GET /api/v1/invoices

List all invoices.

**Query Parameters:**
- `customer_id` (integer): Filter by customer
- `status` (string): Filter by status (draft/sent/partial/paid/overdue)
- `date_from` (date): Filter from date
- `date_to` (date): Filter to date
- `overdue` (boolean): Show only overdue invoices
- `per_page` (integer): Items per page

#### GET /api/v1/invoices/{id}

Get a specific invoice with items and payments.

#### GET /api/v1/invoices/summary

Get invoice summary statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_outstanding": 50000000,
    "overdue_amount": 10000000,
    "paid_this_month": 75000000,
    "by_status": [
      { "status": "draft", "count": 5, "total": 25000000 },
      { "status": "sent", "count": 10, "total": 50000000 },
      { "status": "paid", "count": 50, "total": 250000000 }
    ]
  }
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## Webhooks

### Xendit Payment Webhook

```
POST /api/webhooks/xendit/invoice
```

Receives payment notifications from Xendit. Configure this URL in your Xendit dashboard.

---

## SDK Examples

### PHP (Guzzle)

```php
$client = new GuzzleHttp\Client([
    'base_uri' => 'https://your-domain.com/api/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ],
]);

// Get customers
$response = $client->get('customers', [
    'query' => ['search' => 'john']
]);
$customers = json_decode($response->getBody(), true);

// Create customer
$response = $client->post('customers', [
    'json' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]
]);
```

### JavaScript (Fetch)

```javascript
const API_URL = 'https://your-domain.com/api/v1';
const TOKEN = 'your-api-token';

// Get customers
const response = await fetch(`${API_URL}/customers?search=john`, {
  headers: {
    'Authorization': `Bearer ${TOKEN}`,
    'Accept': 'application/json',
  },
});
const data = await response.json();

// Create customer
const response = await fetch(`${API_URL}/customers`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${TOKEN}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
  }),
});
```

### cURL

```bash
# Get customers
curl -X GET "https://your-domain.com/api/v1/customers" \
  -H "Authorization: Bearer your-token" \
  -H "Accept: application/json"

# Create customer
curl -X POST "https://your-domain.com/api/v1/customers" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```
