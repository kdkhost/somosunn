# UNN - Eventos / Apadrinhamento (esqueleto)

Este repositório contém o esqueleto do sistema (Laravel 10 + Inertia + React + Tailwind + PWA).

Passos iniciais para rodar localmente (recomendado com Docker):

1. Copie `.env.example` para `.env` e configure as credenciais do banco.
2. Rodar via Docker Compose (se optar pelo contêiner):
   - docker compose up -d --build
3. No contêiner PHP: `composer install` e `php artisan key:generate` e `php artisan migrate --seed`.
4. Frontend: `npm install` e `npm run dev` (ou usar `npm run build` para produção).

Quando você criar o banco, me passe as credenciais (host, db, user, password) que eu ajusto o `.env` e executo as migrations/seeders aqui se desejar.

---

Funcionalidades deste esqueleto:
- Autenticação (admin / pastor / membro / responsável) com roles
- CRUD eventos, crianças, apadrinhamentos
- Frontend PWA com instalabilidade mínima
- API REST (Laravel Sanctum) para integrações mobile/PWA


