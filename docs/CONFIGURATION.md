# Configuration Guide

System configuration and environment setup.

## Environment Variables

### Application

```env
APP_NAME="ERP System"
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### Database

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_database
DB_USERNAME=erp_user
DB_PASSWORD=secure_password
```

### Cache

```env
CACHE_STORE=database
# For Redis (recommended for production):
# CACHE_STORE=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

### Queue

```env
QUEUE_CONNECTION=database
# For Redis (recommended for production):
# QUEUE_CONNECTION=redis
```

### Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Payment Gateway (Xendit)

```env
XENDIT_SECRET_KEY=xnd_development_xxx
XENDIT_PUBLIC_KEY=xnd_public_development_xxx
XENDIT_WEBHOOK_TOKEN=your-webhook-token
XENDIT_CALLBACK_URL=https://your-domain.com/api/webhooks/xendit/invoice
```

---

## Application Configuration

### config/app.php

Key settings:
- `timezone`: Application timezone (default: 'UTC')
- `locale`: Default language (default: 'en')
- `fallback_locale`: Fallback language

### config/database.php

Database connection settings. PostgreSQL is the default.

### config/cache.php

Cache configuration. Supports:
- `database`: Default, uses database table
- `redis`: Recommended for production
- `file`: File-based caching
- `array`: In-memory (testing only)

### config/queue.php

Queue configuration for background jobs:
- `database`: Default, uses database table
- `redis`: Recommended for production
- `sync`: Synchronous (development only)

---

## Multi-Currency Configuration

### Default Currencies

The system comes with pre-configured currencies:

| Code | Name | Symbol | Exchange Rate |
|------|------|--------|---------------|
| IDR | Indonesian Rupiah | Rp | 1.000000 (base) |
| USD | US Dollar | $ | 0.000063 |
| EUR | Euro | € | 0.000058 |
| SGD | Singapore Dollar | S$ | 0.000085 |
| MYR | Malaysian Ringgit | RM | 0.000280 |

### Adding New Currencies

```php
use App\Models\Currency;

Currency::create([
    'code' => 'JPY',
    'name' => 'Japanese Yen',
    'symbol' => '¥',
    'exchange_rate' => 0.0094,
    'decimal_places' => 0,
    'decimal_separator' => '.',
    'thousand_separator' => ',',
    'symbol_position' => 'before',
    'is_active' => true,
]);
```

### Updating Exchange Rates

```php
use App\Services\CurrencyService;

// Manual update
CurrencyService::updateRate('IDR', 'USD', 0.000063, now(), 'manual');

// Or update currency directly
$currency = Currency::where('code', 'USD')->first();
$currency->update(['exchange_rate' => 0.000063]);
```

---

## Inventory Configuration

### Low Stock Threshold

Set in `config/inventory.php` or environment:

```env
INVENTORY_LOW_STOCK_THRESHOLD=10
```

### Warehouse Settings

Configure default warehouse behavior in settings.

---

## Invoice Configuration

### Payment Terms

Default payment terms can be configured:
- Net 7 days
- Net 14 days
- Net 30 days
- Net 60 days
- Due on receipt

### Invoice Numbering

Format: `INV-YYYYMM-XXXX`

Customize in `App\Models\Invoicing\Invoice`:

```php
public static function generateInvoiceNumber(): string
{
    $prefix = 'INV';
    $year = now()->format('Y');
    $month = now()->format('m');
    
    $lastInvoice = static::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->orderByDesc('id')
        ->first();
    
    $sequence = $lastInvoice 
        ? (int) substr($lastInvoice->invoice_number, -4) + 1 
        : 1;
    
    return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
}
```

---

## HR Configuration

### Leave Types

Default leave types:
- Annual Leave (12 days/year)
- Sick Leave (12 days/year)
- Personal Leave (3 days/year)
- Maternity Leave (90 days)
- Paternity Leave (7 days)

### Payroll Components

Configure salary components:
- **Earnings**: Basic salary, allowances, overtime, bonuses
- **Deductions**: Tax, insurance, loans

---

## Security Configuration

### Rate Limiting

API rate limits (configurable in `ApiRateLimiter` middleware):

| Type | Limit | Window |
|------|-------|--------|
| Standard | 60 requests | 1 minute |
| Heavy | 10 requests | 1 minute |
| Export | 5 requests | 5 minutes |

### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
```

### CORS Configuration

Configure in `config/cors.php` for API access.

---

## Performance Optimization

### Caching Strategy

1. **Dashboard Data**: 5-minute cache
2. **Report Data**: 5-minute cache
3. **Currency Data**: 1-hour cache
4. **Model Data**: 1-hour cache

### Database Optimization

Recommended indexes are created by migrations. For large datasets:

```sql
-- Additional indexes for performance
CREATE INDEX idx_sales_orders_customer_date ON sales_orders(customer_id, order_date);
CREATE INDEX idx_invoices_customer_status ON invoices(customer_id, status);
CREATE INDEX idx_products_category_status ON products(category_id, status);
```

### Queue Workers

For production, run queue workers:

```bash
php artisan queue:work --queue=high,default,low --tries=3
```

Or use Supervisor:

```ini
[program:erp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=high,default,low --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/logs/worker.log
```

---

## Scheduled Tasks

Configure in `routes/console.php`:

```php
// Clear dashboard cache hourly
Schedule::call(function () {
    \App\Services\DashboardService::clearCache();
})->hourly();

// Check overdue invoices daily
Schedule::command('invoices:check-overdue')->dailyAt('08:00');

// Cleanup old activity logs monthly
Schedule::call(function () {
    \App\Services\ActivityLogService::cleanup(90);
})->monthly();
```

Run scheduler:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Backup Configuration

### Database Backup

```bash
# PostgreSQL backup
pg_dump -U username -h localhost database_name > backup.sql

# Restore
psql -U username -h localhost database_name < backup.sql
```

### File Backup

Backup these directories:
- `storage/app` - Uploaded files
- `.env` - Environment configuration

---

## Logging

### Log Channels

Configure in `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'days' => 14,
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'level' => 'error',
    ],
],
```

### Slow Query Logging

Enable in your service provider:

```php
use App\Services\PerformanceService;

public function boot()
{
    if (config('app.debug')) {
        PerformanceService::logSlowQueries(100); // Log queries > 100ms
    }
}
```

---

## Artisan Commands

### Cache Management

```bash
# Warm cache
php artisan cache:manage warm

# Clear cache
php artisan cache:manage clear

# View stats
php artisan cache:manage stats

# Target specific module
php artisan cache:manage warm --module=dashboard
php artisan cache:manage clear --module=reports
```

### System Validation

```bash
php artisan system:validate
```

### Activity Log Cleanup

```bash
php artisan activity:cleanup --days=90
```

### Standard Laravel Commands

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```
