# Design Document: Fix Activity Causer Property Bug

## Overview

This design addresses the "Undefined property: stdClass::$causer" error occurring on the invoice edit page. The fix standardizes the activity data structure returned by the `WithNotes` trait to match the expected format of the `activity-item.blade.php` component.

## Architecture

### Current Data Flow

```
WithNotes Trait → Returns activities with 'causer_name' (string)
       ↓
Invoice Form → Passes to view as $activities
       ↓
activity-item.blade.php → Expects $activity->causer (object)
       ↓
❌ Error: Undefined property: stdClass::$causer
```

### Fixed Data Flow

```
WithNotes Trait → Returns activities with 'causer' (object)
       ↓
Invoice Form → Passes to view as $activities
       ↓
activity-item.blade.php → Accesses $activity->causer->name
       ↓
✅ Works correctly
```

## Components and Interfaces

### WithNotes Trait (Modified)

Location: `app/Livewire/Concerns/WithNotes.php`

The `getActivitiesAndNotesProperty()` method will be updated to return a properly structured `causer` object.

**Current Implementation:**
```php
->map(fn($activity) => [
    'type' => 'activity',
    'data' => (object) [
        'id' => $activity->id,
        'action' => $activity->action,
        'description' => $activity->description,
        'properties' => json_decode($activity->properties ?? '{}', true),
        'causer_name' => $activity->causer_name ?? $activity->user_name ?? 'System',
        'created_at' => \Carbon\Carbon::parse($activity->created_at),
    ],
    'created_at' => \Carbon\Carbon::parse($activity->created_at),
]);
```

**Fixed Implementation:**
```php
->map(fn($activity) => [
    'type' => 'activity',
    'data' => (object) [
        'id' => $activity->id,
        'action' => $activity->action,
        'event' => $activity->action,
        'description' => $activity->description,
        'properties' => json_decode($activity->properties ?? '{}', true),
        'user_id' => $activity->user_id,
        'user_name' => $activity->causer_name ?? $activity->user_name ?? 'System',
        'causer' => (object) [
            'id' => $activity->user_id,
            'name' => $activity->causer_name ?? $activity->user_name ?? 'System',
        ],
        'created_at' => \Carbon\Carbon::parse($activity->created_at),
    ],
    'created_at' => \Carbon\Carbon::parse($activity->created_at),
]);
```

### activity-item.blade.php Component (No Changes Required)

Location: `resources/views/components/ui/activity-item.blade.php`

The component already handles both formats gracefully:
```php
$causer = $activity->causer ?? null;
if (!$causer && isset($activity->user_id)) {
    $causer = (object) [
        'id' => $activity->user_id,
        'name' => $activity->user_name ?? 'System',
    ];
}
```

By providing both `causer` object AND `user_id`/`user_name` fallbacks, we ensure maximum compatibility.

## Data Models

### Activity Data Structure

```php
[
    'type' => 'activity',
    'data' => (object) [
        'id' => int,                    // Activity log ID
        'action' => string,             // 'created', 'updated', 'deleted'
        'event' => string,              // Alias for action (component compatibility)
        'description' => string,        // Human-readable description
        'properties' => array,          // Old/new values for changes
        'user_id' => int|null,          // User ID who performed action
        'user_name' => string,          // User name (fallback)
        'causer' => (object) [          // User object
            'id' => int|null,
            'name' => string,
        ],
        'created_at' => Carbon,         // Timestamp
    ],
    'created_at' => Carbon,             // For sorting
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do.*

Since this is a bug fix with a straightforward data structure change, the testable properties focus on data integrity:

**Property 1: Causer object structure**
*For any* activity returned by `getActivitiesAndNotesProperty()`, the `data->causer` property SHALL be an object with `id` and `name` properties.
**Validates: Requirements TR-1**

**Property 2: Causer name fallback**
*For any* activity where the user is not found in the database, the `causer->name` SHALL default to "System".
**Validates: Requirements US-1**

**Property 3: Backward compatibility**
*For any* activity returned by `getActivitiesAndNotesProperty()`, both `user_id`/`user_name` AND `causer` object SHALL be present for maximum component compatibility.
**Validates: Requirements US-2**

## Error Handling

- If `user_id` is null, `causer->id` will be null and `causer->name` will be "System"
- If the user record doesn't exist (deleted user), the join returns null and falls back to `user_name` from activity_logs or "System"
- JSON decode failures for properties default to empty array `[]`

## Testing Strategy

### Manual Testing
1. Navigate to `/invoicing/invoices/1/edit`
2. Verify page loads without errors
3. Verify activity timeline displays correctly
4. Verify user avatars and names appear for each activity

### Unit Testing (Optional)
- Test `getActivitiesAndNotesProperty()` returns correct structure
- Test fallback to "System" when user is null
- Test properties JSON parsing

Since this is a simple data structure fix, manual verification is sufficient. The fix aligns the trait with the working implementation in `HR/Leave/Requests/Form.php`.
