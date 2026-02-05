# Tasks: Implementação Multi-Tenant RBAC

- [x] Create `HasFeatureAccess` Trait (refactoring/merging `HasPackageAccess`) <!-- id: 0 -->
- [x] Create `CheckFeature` Middleware <!-- id: 1 -->
- [x] Update `User` model to use `HasFeatureAccess` <!-- id: 2 -->
- [x] Update `Plan` model to support `features` attribute (accessor) <!-- id: 3 -->
- [x] Refactor `sidebar.blade.php` to use dynamic feature config <!-- id: 4 -->
- [x] Update `routes/web.php` with `check.feature:xxx` middleware groups <!-- id: 5 -->
- [x] Create Seeder/Migration for default Plan Features <!-- id: 6 -->
- [ ] Manual Verification: Check Feature Isolation (Admin vs Member) <!-- id: 7 -->
- [ ] Manual Verification: Check Member Plan Differentiation (VIP vs Free) <!-- id: 8 -->
