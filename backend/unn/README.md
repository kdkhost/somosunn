# UNN - Eventos / Apadrinhamento (esqueleto)

Este repositório contém o esqueleto do sistema (Laravel 10 + Inertia + React + Tailwind + PWA).

Passos iniciais para rodar localmente (recomendado com Docker):

1. Copie `.env.example` para `.env` e configure as credenciais do banco.
2. Rodar via Docker Compose (se optar pelo contêiner):
   - docker compose up -d --build
3. No contêiner PHP: `composer install` e `php artisan key:generate` e `php artisan migrate --seed`.
4. Frontend: `npm install` e `npm run dev` (ou usar `npm run build` para produção).

When you create the database, pass the credentials (host, db, user, password) or note that I've already set the `.env.example` to use the provided values and chosen host `127.0.0.1` (localhost). The repository will no longer contain an exposed IP address.

If you want to run using Docker, the `docker-compose.yml` still contains a MySQL service which you can start locally; when running inside containers the DB host will be `db` in the compose network. Use `.env` to control which mode you want.

NOTE: You asked for PHP 8.4 — I updated the Dockerfile base to `php:8.4-fpm` and set `composer.json` to require `php:^8.4`. If your environment doesn't yet have PHP 8.4, tell me and I can adjust back to your required version.

---

Funcionalidades deste esqueleto:
- Autenticação (admin / pastor / membro / responsável) com roles
- CRUD eventos, crianças, apadrinhamentos
- Frontend PWA com instalabilidade mínima
- API REST (Laravel Sanctum) para integrações mobile/PWA


