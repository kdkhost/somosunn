# Spec: Manual Plan Assignment

## Requirements

### 1. User Model Updates
- Ensure `User` model has:
    - `plan_id`: foreign key to `plans` (nullable).
    - `plan_expires_at`: datetime (nullable).
    - Method `hasActiveManualPlan()`: returns true if `plan_id` is set and `plan_expires_at` is null (lifetime) or in the future.

### 2. Admin User Edit Interface
- On the user edit page (`admin.users.edit`), add a new "Subscription Management" section.
- **Fields**:
    - `Plan`: Select dropdown with all active plans.
    - `Expiry Date`: Date picker (optional, if empty = lifetime/indefinite).
    - `Action`: "Apply Plan" button.

### 3. Logic for Manual Overrides
- When an admin updates a user's plan manually:
    - It should not interfere with Stripe/MercadoPago automated subscriptions if they exist (or it should act as a "VIP" override).
    - Audit log: Record that admin X changed plan of user Y.

### 4. Integration with Access Traits
- Update `HasFeatureAccess` or `canAccessFeature` to check the manual `plan_id` if no active subscription record exists in the `subscriptions` table.

## Acceptance Criteria
- [ ] Admin goes to User "John Doe" and selects "Premium Plan".
- [ ] John Doe instantly gains access to Premium features without paying a cent.
- [ ] If the admin sets an expiry date for yesterday, John Doe's access is revoked (middleware redirects him to `/premium`).
- [ ] Manual assignment works even if the user never navigated to the checkout page.
