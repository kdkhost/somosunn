# Spec: Profile Access and Navigation Fix

## Requirements

### 1. Correct "My Profile" Link
- Verify the "Meu Perfil" link in the sidebar and navbar.
- Ensure it points to `admin.profile.edit` (the member's own edit page) instead of a landing page like `/portal`.

### 2. Middleware Whitelisting
- The `EnsureUserHasActivePlan` middleware MUST explicitly allow access to:
    - `admin.profile.edit`
    - `admin.profile.update`
- **Reasoning**: A user whose plan has expired or who just registered MUST be able to see their own account details to understand their status or update their info.

### 3. Loop Prevention
- Check for any `Redirect::to('/portal')` in the `AdminMiddleware` or `SocialController` that triggers based on "User Role".
- Fix logic where members were being forced to `/portal` regardless of the route they were trying to access. Members should be able to stay on `/admin/profile` if that's where they clicked.

### 4. Sidebar Uniformity
- Ensure the sidebar menu highlights correctly when on the profile page.

## Acceptance Criteria
- [ ] User clicks "Meu Perfil" and the URL is `/admin/profile`.
- [ ] User remains on `/admin/profile` and can see their fields (Bio, Photo, etc.).
- [ ] An unauthenticated user (no plan) can still access `/admin/profile` (if they are logged in) to manage their basic account data.
