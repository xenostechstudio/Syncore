# Fix Activity Causer Property Bug

## Problem Statement

When editing an invoice at `/invoicing/invoices/1/edit`, the following error occurs:

```
Undefined property: stdClass::$causer
```

The error originates from the `activity-item.blade.php` component which expects an activity object with a `causer` property containing a user object (with `name`, `id` properties), but the `WithNotes` trait returns activities with only a `causer_name` string property.

## Root Cause Analysis

### Current Implementation in `WithNotes` Trait

The `getActivitiesAndNotesProperty()` method in `app/Livewire/Concerns/WithNotes.php` returns activity data structured as:

```php
'data' => (object) [
    'id' => $activity->id,
    'action' => $activity->action,
    'description' => $activity->description,
    'properties' => json_decode($activity->properties ?? '{}', true),
    'causer_name' => $activity->causer_name ?? $activity->user_name ?? 'System',  // ❌ String, not object
    'created_at' => \Carbon\Carbon::parse($activity->created_at),
],
```

### Expected Structure by `activity-item.blade.php`

The component at `resources/views/components/ui/activity-item.blade.php` expects:

```php
$causer = $activity->causer ?? null;  // Expects object with 'name' property
if (!$causer && isset($activity->user_id)) {
    $causer = (object) [
        'id' => $activity->user_id,
        'name' => $activity->user_name ?? 'System',
    ];
}
```

### Working Implementation (HR Leave Requests)

The `app/Livewire/HR/Leave/Requests/Form.php` correctly implements this:

```php
'causer' => (object) ['name' => $activity->causer_name ?? $activity->user_name ?? 'System'],
```

## User Stories

### US-1: Fix Activity Display on Invoice Edit Page
**As a** user editing an invoice  
**I want** to see the activity log without errors  
**So that** I can track changes made to the invoice

**Acceptance Criteria:**
- Invoice edit page loads without "Undefined property: stdClass::$causer" error
- Activity timeline displays correctly with user names
- User avatars render properly for each activity entry
- System activities show "System" as the actor when no user is associated

### US-2: Consistent Activity Data Structure Across All Modules
**As a** developer  
**I want** the `WithNotes` trait to return properly structured activity data  
**So that** all modules using this trait work correctly with the `activity-item` component

**Acceptance Criteria:**
- `WithNotes` trait returns `causer` as an object with `name` and `id` properties
- All Livewire components using `WithNotes` display activities correctly
- No breaking changes to existing functionality

## Technical Requirements

### TR-1: Update WithNotes Trait
Modify `app/Livewire/Concerns/WithNotes.php` to return `causer` as an object:

```php
'data' => (object) [
    'id' => $activity->id,
    'action' => $activity->action,
    'description' => $activity->description,
    'properties' => json_decode($activity->properties ?? '{}', true),
    'causer' => (object) [
        'id' => $activity->user_id,
        'name' => $activity->causer_name ?? $activity->user_name ?? 'System',
    ],
    'created_at' => \Carbon\Carbon::parse($activity->created_at),
],
```

### TR-2: Verify All Components Using WithNotes
Ensure the following components work correctly after the fix:
- `app/Livewire/Invoicing/Invoices/Form.php`
- Any other components using the `WithNotes` trait

## Files to Modify

1. `app/Livewire/Concerns/WithNotes.php` - Fix the activity data structure

## Testing Checklist

- [ ] Invoice edit page loads without errors
- [ ] Activity timeline displays user names correctly
- [ ] User avatars render for activities with associated users
- [ ] "System" displays for activities without associated users
- [ ] Notes display correctly with user information
- [ ] Other modules using `WithNotes` continue to work

## Priority

**High** - This is a runtime error that prevents users from editing invoices.
