# Development Guide

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL 14+

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd <project-directory>

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## Development Workflow

### Running the Application

```bash
# Start Laravel development server
php artisan serve

# Start Vite development server (in separate terminal)
npm run dev
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Run seeders
php artisan db:seed
```

### Code Quality

```bash
# Run PHPStan (static analysis)
./vendor/bin/phpstan analyse

# Run Pint (code formatting)
./vendor/bin/pint

# Run tests
php artisan test
```

## Creating New Features

### Adding a New Model

1. Create the model with migration:
```bash
php artisan make:model ModuleName/ModelName -m
```

2. Add the `LogsActivity` trait for audit logging:
```php
use App\Traits\LogsActivity;

class ModelName extends Model
{
    use LogsActivity;
    
    protected array $logActions = ['created', 'updated', 'deleted'];
}
```

3. Add `HasNotes` and `HasAttachments` if needed:
```php
use App\Traits\HasNotes;
use App\Traits\HasAttachments;

class ModelName extends Model
{
    use LogsActivity, HasNotes, HasAttachments;
}
```

### Adding a New Livewire Component

1. Create Index component:
```bash
php artisan make:livewire ModuleName/SubModule/Index
```

2. Create Form component:
```bash
php artisan make:livewire ModuleName/SubModule/Form
```

3. Follow existing patterns for search, pagination, and bulk actions.

### Adding an Export Class

```php
<?php

namespace App\Exports;

use App\Models\ModuleName\ModelName;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ModelNamesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return ModelName::query()
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                // ... other fields
            ]);
    }

    public function headings(): array
    {
        return ['Name', /* ... */];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
```

### Adding an Import Class

```php
<?php

namespace App\Imports;

use App\Models\ModuleName\ModelName;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ModelNamesImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');
                if (empty($name)) continue;

                ModelName::updateOrCreate(
                    ['name' => $name],
                    [/* ... other fields */]
                );
                
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
```

## PostgreSQL Considerations

### Case-Insensitive Search
Always use `ilike` instead of `like` for case-insensitive searches:

```php
// Correct
$query->where('name', 'ilike', "%{$search}%");

// Incorrect (case-sensitive in PostgreSQL)
$query->where('name', 'like', "%{$search}%");
```

### Unique Validation with Nullable IDs
Use `Rule::unique()->ignore()` for update validation:

```php
use Illuminate\Validation\Rule;

'email' => [
    'required',
    'email',
    Rule::unique('users')->ignore($this->userId),
],
```

## UI/UX Guidelines

### Icons
Use Flux icons:
```blade
<flux:icon name="plus" class="size-4" />
<flux:icon name="pencil" class="size-4" />
<flux:icon name="trash" class="size-4" />
```

### Buttons
Primary button styling:
```blade
<button class="bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900">
    Save
</button>
```

### Delete Confirmation Modals
Use custom Alpine.js modals:
```blade
@isset($showDeleteConfirm)
<x-ui.delete-modal 
    :show="$showDeleteConfirm" 
    :validation="$deleteValidation ?? []"
/>
@endisset
```

### Form Behavior
- Don't redirect after save (except create → edit redirect)
- Show success/error messages via flash

## Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/SalesOrderTest.php

# Run with coverage
php artisan test --coverage
```

### Writing Tests
```php
public function test_can_create_sales_order(): void
{
    $customer = Customer::factory()->create();
    
    $response = $this->actingAs($this->user)
        ->post('/sales/orders', [
            'customer_id' => $customer->id,
            // ...
        ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'customer_id' => $customer->id,
    ]);
}
```

## Troubleshooting

### Common Issues

1. **Migration errors**: Ensure PostgreSQL is running and credentials are correct
2. **Permission errors**: Run `php artisan permission:cache-reset`
3. **Asset compilation**: Run `npm run build` after pulling changes
4. **Cache issues**: Run `php artisan cache:clear && php artisan config:clear`

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Regenerate autoload
composer dump-autoload

# View routes
php artisan route:list

# View registered Livewire components
php artisan livewire:discover
```
