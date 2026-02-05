# Spec: Plan Management

## Requirements

### 1. Database Schema Completion
- Verify that the `plans` table has all necessary columns:
    - `name`: string
    - `price`: decimal
    - `period`: string ('monthly', 'yearly')
    - `highlight`: boolean
    - `benefits`: json/array (for the list of features shown on the card)
    - `permissions`: json/array (for the RBAC feature access keys like 'chat', 'courses', etc.)
    - `is_active`: boolean
    - `image`: string (optional, for custom icons/images)

### 2. Administrator CRUD
- **Index**: List all plans with their status and price.
- **Create/Edit**: Form to manage all fields including a dynamic UI for "Benefits" (list of strings) and "Permissions" (checkboxes for available features).
- **Toggle Status**: Ability to archive/deactivate a plan without deleting data.

### 3. Feature Mapping
- Map specific features to keys that the `HasFeatureAccess` trait can understand:
    - `chat`
    - `courses`
    - `events`
    - `mentorships`
    - `community` (feed social)

### 4. Public Pricing Synchronization
- The `/premium` page must iterate over `Plan::where('is_active', true)->get()`.
- The layout must adapt to the number of plans (grid-cols-2, grid-cols-3, etc.).
- "Highlight" flag should visually emphasize the primary plan (e.g., "Most Popular").

## Acceptance Criteria
- [ ] Admin can create a new plan called "Gold" and it appears instantly on the `/premium` page.
- [ ] Changing a plan price in the admin panel reflects on the public site.
- [ ] Deactivating a plan hides it from the public view.
- [ ] Plan permissions correctly gate features for users associated with that plan.
