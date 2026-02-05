# Proposal: Mandatory Subscriptions and Plan Management

## Why

Currently, the platform allows users to register and access a general dashboard without a clear paywall or mandatory plan selection. This leads to several issues:
1.  **Monetization Leak**: Users can bypass the "choose a plan" step and access features without paying.
2.  **Navigation Confusion**: Clicking on "My Profile" sometimes redirects users to the `/portal` inappropriately, preventing them from managing their own data.
3.  **Static Pricing**: The `/premium` page is mostly static, making it difficult for administrators to update prices or benefits without code changes.
4.  **Admin Limitations**: Admins cannot manually grant access or link plans to users directly from the panel.

## What Changes

### 1. Mandatory Plan Selection (Paywall Middleware)
- **Middleware Implementation**: Create an `EnsureUserHasActivePlan` middleware.
- **Redirect Logic**: If an authenticated member (non-admin) does not have an active subscription or an associated plan, they will be forcibly redirected to a plan selection page (refactored `/premium`).
- **Access Lockdown**: All portal routes (except for profile management and billing/payment) will be protected by this middleware.

### 2. Corrected Navigation
- **Profile Redirection Fix**: Ensure that when a member clicks "My Profile", they are taken to their actual profile editing/viewing page (`/admin/profile`) instead of being redirected to `/portal`.
- **Dashboard Separation**: Clarify the distinction between the "Admin Dashboard" (for gestors) and the "User Panel" (for members).

### 3. Dynamic Plan Management (Admin CRUD)
- **Database Refinement**: Ensure `plans` table supports all fields displayed on the `/premium` page:
    - Name, Price, Period (Monthly/Annual), Highlights, Benefits (JSON), Permissions (JSON), and Status.
- **Admin Interface**: Build a comprehensive CRUD in the admin panel to:
    - Create/Edit/Delete plans.
    - Toggle which plans are "Featured" or "Popular".
    - Configure specific permissions (e.g., "chat", "courses", "events") per plan.
- **Dynamic Pricing Page**: Update `resources/views/site/premium.blade.php` to fetch plans from the database.

### 4. Admin Manual Linking
- **User Management Update**: Add a "Plan Association" tool in the User Edit screen.
- **Admin Bypass**: Allow Admins/Superadmins to manually set a user's plan and expiration date.

### 5. FullCalendar v4 e Exibição de Eventos Reais
- **Conserto do FullCalendar**: Garantir que o FullCalendar v4 no painel administrativo e no dashboard carregue corretamente os eventos do banco de dados (revisão de feeds AJAX e dependências JS).
- **Eventos Reais no Front-end**: Atualizar a Home (`site.index`) e a página de Eventos (`events.index`) para exibir todos os eventos publicados (pagos e gratuitos) recuperados do banco de dados, mantendo rigorosamente a identidade visual atual ("sem mudar uma vírgula").

## Capabilities

- **spec-plan-management**: Administrators can manage all plans and pricing through a central dashboard.
- **spec-mandatory-subscription**: New members must pay or select a plan before accessing community features.
- **spec-manual-assignment**: Admins can manually link plans/packages to any user without them having to go through checkout.
- **spec-profile-access**: Members can always access their own profile settings regardless of subscription status.
- **spec-calendar-fix**: FullCalendar v4 correctly fetches and displays database events in both Admin and Dashboard.
- **spec-real-events-visibility**: Both paid and free events from the database are reflected in the public front-end.

## Impact

### 👥 User Experience
- **Structured Onboarding**: New users have a clear path from registration to payment.
- **Clarity**: Users understand exactly what benefits they are paying for.
- **Information Accuracy**: Users see real, up-to-date events instead of demo/placeholders.

### ⚙️ Systems
- **Revenue Control**: Hard gate for premium features ensures better conversion.
- **Flexibility**: Marketing team can change prices or add new plan tiers (e.g., "Silver", "Gold") without developer intervention.

### 🔒 Security
- **RBAC Strengthening**: Permissions are now strictly tied to the database-driven plan assignments.
- **Admin Control**: Manual overrides ensure support teams can fix access issues directly.
