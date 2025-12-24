# Permissions Architecture Enhancement

## Overview
Consolidate the permissions system by removing ambiguous inline permissions from the User Form and implementing a clear Role-Based Access Control (RBAC) using Spatie Permission package. The Roles & Permissions form will be enhanced to show clear access levels per module.

## Problem Statement
Currently, there are two separate permission systems that create confusion:
1. **User Form** has inline permission dropdowns (Sales, Purchase, Inventory, Accounting) with levels (No Access, User, Manager, Administrator) - but these are UI-only and not persisted to the database
2. **Roles & Permissions** page uses Spatie Permission package with module access toggles (on/off) - this is the actual working system

This creates ambiguity for users who don't know which permissions actually apply.

## Solution
Implement a clean Role-Based approach:
- Remove inline permissions from User Form
- Add a Role selector to User Form that assigns Spatie roles
- Enhance Roles Form to show clear access levels (No Access, User, Manager, Administrator) per module instead of simple on/off toggles

---

## User Stories

### US-1: User Form Role Assignment
**As an** administrator  
**I want to** assign a role to a user from the User Form  
**So that** I can quickly set their permissions without navigating to a separate page

**Acceptance Criteria:**
- [x] Remove the inline "Permissions" section (Sales, Purchase, Inventory, Accounting dropdowns) from User Form Access Rights tab
- [x] Add a "Role" dropdown/selector in the Access Rights tab
- [x] Role dropdown shows all available roles from Spatie Permission
- [x] Selected role is saved when user is saved
- [x] Display current assigned role when editing existing user
- [x] Allow assigning multiple roles if needed (optional enhancement)

### US-2: Enhanced Role Module Access Levels
**As an** administrator  
**I want to** set specific access levels (No Access, User, Manager, Administrator) for each module when creating/editing a role  
**So that** I can define granular permissions clearly

**Acceptance Criteria:**
- [x] Replace simple on/off toggles with access level selector per module
- [x] Access levels: No Access, User, Manager, Administrator
- [x] Each level grants progressively more permissions within the module
- [x] Visual indication of current access level (e.g., segmented control or dropdown)
- [x] "No Access" is clearly shown as the default/unselected state
- [x] Changes are saved to Spatie permissions when role is saved

### US-3: Permission Naming Convention
**As a** developer  
**I want** a clear permission naming convention for access levels  
**So that** the system can properly check permissions in code

**Acceptance Criteria:**
- [x] Create permissions following pattern: `{module}.{level}` (e.g., `sales.user`, `sales.manager`, `sales.admin`)
- [x] Seed/create these permissions in the database
- [x] Higher levels include lower level permissions (admin > manager > user)
- [x] Module access permissions: `access.{module}` grants basic entry to the module

### US-4: Clear Visual Feedback
**As an** administrator  
**I want** clear visual feedback on permission levels  
**So that** I can easily understand what access each role has

**Acceptance Criteria:**
- [x] Access level selector shows all 4 options clearly
- [x] Current selection is visually highlighted
- [x] "No Access" state is visually distinct (grayed out or different style)
- [ ] Tooltip or description explains what each level can do (optional enhancement)

---

## Technical Requirements

### Database Changes
- Create new permissions for access levels:
  - `sales.user`, `sales.manager`, `sales.admin`
  - `purchase.user`, `purchase.manager`, `purchase.admin`
  - `inventory.user`, `inventory.manager`, `inventory.admin`
  - `invoicing.user`, `invoicing.manager`, `invoicing.admin`
  - `delivery.user`, `delivery.manager`, `delivery.admin`
  - `settings.user`, `settings.manager`, `settings.admin`

### Files to Modify

**User Form:**
- `app/Livewire/Settings/Users/Form.php`
  - Add `$selectedRoles` property
  - Load available roles in `mount()`
  - Load user's current roles when editing
  - Save role assignment in `save()`
- `resources/views/livewire/settings/users/form.blade.php`
  - Remove inline permissions section
  - Add role selector component

**Roles Form:**
- `app/Livewire/Settings/Roles/Form.php`
  - Update `$moduleCards` to support access levels
  - Add method to handle access level selection
  - Update `save()` to sync level-based permissions
- `resources/views/livewire/settings/roles/form.blade.php`
  - Replace toggle buttons with access level selector
  - Add visual styling for different levels

**Seeder/Migration:**
- Create seeder for new access level permissions

---

## UI/UX Design Notes

### User Form - Role Selector
```
Access Rights Tab:
┌─────────────────────────────────────────┐
│ Role                                    │
│ ┌─────────────────────────────────────┐ │
│ │ Select role...              ▼       │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ Status                                  │
│ ☑ Active User                          │
└─────────────────────────────────────────┘
```

### Roles Form - Module Access Levels
```
Module Access Tab:
┌─────────────────────────────────────────────────────────────┐
│ 🛒 Sales                                                    │
│ Quotations, orders, customers, and teams                    │
│                                                             │
│ ○ No Access  ○ User  ○ Manager  ● Administrator            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ 📦 Inventory                                                │
│ Transfers, adjustments, products, warehouses                │
│                                                             │
│ ● No Access  ○ User  ○ Manager  ○ Administrator            │
└─────────────────────────────────────────────────────────────┘
```

---

## Out of Scope
- Permission inheritance between roles
- Team-based permissions
- Custom permission creation UI
- Permission caching optimization
