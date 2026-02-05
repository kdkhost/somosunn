# Design: Sistema Multi-Tenant RBAC Unificado

## Objetivo
Implementar um sistema de controle de acesso unificado que combine Roles (Papéis) e Entitlements (Direitos via Pacotes/Planos), garantindo que cada membro veja e acesse apenas o que sua conta permite, com isolamento visual e lógico entre funcionalidades.

## Estrutura Atual vs. Nova
*   **Atual**:
    *   `HasRoles`: Verifica roles (`admin`, `member`) e permissions diretas no DB.
    *   `HasPackageAccess`: Verifica acesso a *Cursos* específicos via `Order`/`Enrollment`.
    *   *Problema*: Falta controle granular sobre *Funcionalidades* (ex: Whatsapp, Eventos, Mentorias) que dependem do Plano contratado, e a UI não se adapta bem a isso.

*   **Nova Abordagem (Unified Matrix)**:
    *   **Features (Funcionalidades)**: Strings identificadoras (ex: `feature:whatsapp`, `feature:mentorships`, `feature:events`).
    *   **Fonte de Verdade**:
        1.  **Roles**: Admin tem tudo (`*`).
        2.  **Planos**: Cada `Plan` terá uma lista de `features` permitidas no JSON `permissions`.
        3.  **Assinatura Ativa**: O User herda as features do Plano ativo.
    *   **Trait Unificado**: `HasFeatureAccess` (extensão ou refatoração do `HasPackageAccess`).

## Matriz de Permissões (Exemplo)

| Feature | Admin | Membro (Grátis) | Membro (VIP) |
| :--- | :--- | :--- | :--- |
| `panel.admin` | ✅ | ❌ | ❌ |
| `course.view` | ✅ | (Apenas Comprados) | (Apenas Comprados) |
| `social.feed` | ✅ | ✅ | ✅ |
| `social.groups` | ✅ | ❌ | ✅ |
| `mentorship.list` | ✅ | ✅ (Só visualiza) | ✅ |
| `events.calendar` | ✅ | ✅ | ✅ |

## Workflow de Implementação

1.  **Backend Core**:
    *   Atualizar `Plan` model helper methods (`getFeaturesAttribute`).
    *   Criar/Refatorar `HasFeatureAccess` no User.
        *   `canAccess($feature)`: Verifica Admin -> Verifica Plano Ativo.
    *   Middleware `CheckFeature:feature_name`.

2.  **Frontend Dinâmico**:
    *   Sidebar Generator: Recebe lista de menus e filtra via `canAccess`.
    *   Routes: Agrupar rotas de funcionalidades sob middleware.

3.  **Isolamento**:
    *   Garantir que rotas de Admin (`/admin/*`) sejam estritas.
    *   Garantir que rotas de Membro (`/portal`, `/cursos`) respeitem a feature.

4.  **Migração**:
    *   Script para atribuir features padrão aos planos existentes.

## Arquivos Afetados
*   `app/Models/User.php`
*   `app/Models/Plan.php`
*   `app/Http/Middleware/CheckFeature.php` (Novo)
*   `resources/views/admin/partials/sidebar.blade.php` (Refatoração completa para array de config)
*   `routes/web.php`
