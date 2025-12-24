# Activity Logging Best Practices

## Overview
Activity logging (audit trail) should be centralized and reusable across all modules. Instead of hardcoding activity logs in each Livewire component, use a dedicated system.

## Recommended Approach

### Option 1: Use Spatie Activity Log Package (Recommended)
The `spatie/laravel-activitylog` package is the industry standard for Laravel activity logging.

**Installation:**
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

**Usage in Models:**
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

**Benefits:**
- Automatic logging on create/update/delete
- Tracks which fields changed (old vs new values)
- Tracks who made the change (causer)
- Works with all Eloquent models
- Query activities easily

### Option 2: Custom Activity Log (Lightweight)
If you prefer a simpler solution without external packages:

**Migration:**
```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('log_name')->default('default');
    $table->text('description');
    $table->nullableMorphs('subject'); // The model being logged (User, Product, etc.)
    $table->nullableMorphs('causer');  // Who performed the action (usually User)
    $table->json('properties')->nullable(); // Additional data (old/new values)
    $table->timestamps();
    
    $table->index('log_name');
});
```

**Activity Model:**
```php
class Activity extends Model
{
    protected $table = 'activity_logs';
    protected $casts = ['properties' => 'array'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper to log activity
    public static function log(string $description, ?Model $subject = null, array $properties = []): self
    {
        return static::create([
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'causer_type' => auth()->user() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => $properties,
        ]);
    }
}
```

**Usage:**
```php
// In Livewire save method
Activity::log('User updated', $user, [
    'old' => $user->getOriginal(),
    'new' => $user->toArray(),
]);
```

## Displaying Activity Log

Create a reusable Blade component for the activity timeline:

```blade
{{-- resources/views/components/activity-timeline.blade.php --}}
@props(['activities'])

<div class="space-y-4">
    @forelse($activities as $activity)
        <div class="flex gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                @if($activity->causer)
                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        {{ strtoupper(substr($activity->causer->name, 0, 2)) }}
                    </span>
                @else
                    <flux:icon name="cog-6-tooth" class="size-4 text-zinc-400" />
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $activity->causer?->name ?? 'System' }}
                    </span>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">
                        {{ $activity->created_at->diffForHumans() }}
                    </span>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity->description }}</p>
            </div>
        </div>
    @empty
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
    @endforelse
</div>
```

## Recommendation

**Use Spatie Activity Log** - it's well-maintained, feature-rich, and saves development time. You can:
1. Install the package
2. Add the trait to models you want to track
3. Query activities with `Activity::forSubject($user)->latest()->get()`
4. Display using a reusable component

This approach ensures consistency across all modules (Users, Products, Orders, etc.) without duplicating code.
