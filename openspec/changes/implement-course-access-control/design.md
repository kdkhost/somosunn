# Design: Strict Course Access Control

## Context
The current system allows permission leakage where Admin users viewing Member profiles might trigger permission checks against the Admin's own context inappropriately, or Members might see items they shouldn't. Access needs to be strictly controlled by the active Package/Plan for Members, while Admins maintain full oversight without UI errors. The Admin panel (AdminLTE 3.2) must remain consistent.

## Goals
1.  **Strict Package-Based Access**: Members only access courses/resources defined in their active Package/Plan.
2.  **Admin Isolation**: Admin viewing a Member's profile must not trigger "Access Denied" errors. The view should interpret permissions correctly for the *viewed* context vs the *viewer's* rights.
3.  **UI Consistency**: Hide menu items and buttons that the current user cannot access (Zero "Permission Denied" popups on load).
4.  **No Leaks**: Lower-level users (Members) must never see Admin options.

## Proposed Solution

### 1. Model & Trait Enhancements
*   **`App\Models\Traits\HasRoles`**: Keep focused on Role/Permission (ACL).
*   **New `App\Models\Traits\HasPackageAccess`**:
    *   Create this trait to handle logic for "Does this user own this course?" via Orders/Subscriptions.
    *   Method: `hasCourseAccess($courseId)`
*   **`App\Models\User`**: Include `HasPackageAccess`.

### 2. Policy Refactoring (`CoursePolicy`)
Update `CoursePolicy` to enforce the dual-layer check:
*   **Layer 1 (ACL)**: Does the user have permission to `view_courses`? (Admin: Yes, Member: Yes).
*   **Layer 2 (Ownership)**: Does the user have access to *this specific* course?
    *   **Admin**: Bypass ownership check (Master Key).
    *   **Member**: Check `hasCourseAccess($courseId)`.

### 3. Middleware & Controller
*   Ensure `CheckPermission` handles Route-level protection (already existing, verify it respects `isAdmin`).
*   Controllers must use `authorize('view', $course)` before rendering course details.

### 4. Frontend & Sidebar (AdminLTE)
*   **Sidebar**: Wrap menu items in strict `@can(...)` directives.
    *   Example: Only show "Financials" if `@can('view_financials')`.
*   **Member Panel**:
    *   If a Member does not own a course, either hide it OR show it as "Locked" (depending on business rule). Based on request "so tenha acesso ao itens... compativel com as permissoes", we should likely hide or disable "Access".
    *   **Fix Admin View**: When Admin inspects a Member, ensure the Blade view distinguishes between "My Courses" (the Admin's courses, which is irrelevant) and "User's Courses" (the Member's courses).
    *   *Correction*: If the Admin is viewing the "Member Details" page, it shows *that member's* data. The permission check for *displaying* the data should be "Can the *Viewer* (Admin) see this?" -> YES. The data shown is what the *Member* has.

## Implementation Steps

### Step 1: Backend Logic
1.  Create `HasPackageAccess` trait.
2.  Implement `hasCourseAccess` logic (checking `orders` or `subscriptions` tables - *Need to verify DB schema for this*).
3.  Update `CoursePolicy` to use this check.

### Step 2: Policy & Gate Updates
1.  Define a SuperAdmin/Admin Gate bypass in `AuthServiceProvider` or strict checks in Policy `before` method.

### Step 3: Frontend Cleanup
1.  Modify `resources/views/admin/sidebar.blade.php` (or equivalent) to filter items.
2.  Modify `resources/views/courses/index.blade.php` to use `@can`.
3.  Fix the specific "Admin viewing Member" error:
    *   Locate the view generating the error (likely a component checking `auth()->user()->can(...)` incorrectly on a resource the Admin technically "doesn't own" but should see).

## Verification Plan

### Automated
*   Run existing tests: `php artisan test`

### Manual Verification
1.  **Member Scenario**:
    *   Login as Member.
    *   Check Sidebar: No Admin items.
    *   Check Courses: Can access only purchased courses.
2.  **Admin Scenario**:
    *   Login as Admin.
    *   Check Sidebar: All items visible.
    *   **Drill-down**: Go to Users -> Select Member -> "Inspect/View".
    *   **Pass Condition**: No 403/401 errors, no "Permission Denied" toasts.
