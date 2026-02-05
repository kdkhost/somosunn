# Spec: Mandatory Subscription

## Requirements

### 1. Paywall Middleware
- Implement `EnsureUserHasActivePlan` middleware.
- **Logic**:
    - Allow if user `isAdmin()`.
    - Allow if user has an active subscription (`subscriptions` table) OR has a `plan_id` with a valid `plan_expires_at` (manual assignment).
    - If no plan/active subscription:
        - Check if current route is in the "whitelist" (Login, Register, Logout, Premium/Pricing, Profile Edit, Payment processing).
        - If not whitelisted, redirect to `/premium` with a message: "Choose a plan to continue accessing the community."

### 2. Post-Registration Redirect
- Update the registration controller/flow.
- Immediately after successful registration, redirect the user to the `/premium` page instead of the dashboard.
- Display a "Welcome! Choose your path" message.

### 3. Lockdown of Portal Routes
- Apply the new middleware globally to all routes under the `admin.` prefix, except for specific whitelisted routes like `admin.profile.edit`.
- Ensure common portal features (Chat, Community, Courses) are fully inaccessible without a plan.

### 4. Trial/Grace Period (Optional/Future)
- Design with a "grace period" if needed, but for now, the requirement is "blocking" immediately after registration.

## Acceptance Criteria
- [ ] A new user registers and is instantly taken to `/premium`.
- [ ] Trying to access `/admin` or `/feed` without a plan redirects to `/premium`.
- [ ] An administrator can still access everything without needing a plan.
- [ ] A user can still edit their own profile even without an active plan (to allow them to fix data or see their status).
