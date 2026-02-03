# Plano de Implementação — UNN Platform

Este documento lista as próximas tarefas para completar a plataforma com os requisitos que você descreveu.

1) Instalação inicial
   - `composer install`
   - Configurar `.env` (DB, keys de pagamento, social)
   - `php artisan key:generate` / `php artisan migrate` / `php artisan storage:link`

2) Autenticação (implementado parcialmente)
   - Auth básico com controllers de login/registro e Socialite (Google/Facebook/LinkedIn) — ✅

3) Pagamentos (Gateways)
   - MercadoPago: SDK instalado e serviço helper criado; webhook handler placeholder implementado — ✅ (sandbox handlers pending testing)
   - PagSeguro: placeholder service created; webhook handler placeholder implemented — ✅ (needs webhook validation implementation)
   - Implementar orders, payments table (migrations created) and retry logic — ✅ (migrations present)

4) E-mail
   - PHPMailer instalado as dependency and admin UI to test SMTP added (route `/admin/mail-test`) — ✅

5) Uploads e vídeo
   - Upload chunked endpoint + assemble implemented; admin chunked upload tester provided (`/admin/upload-test`) — ✅
   - Config file for formats/limits: `config/uploads.php` — ✅

6) Frontend
   - Front pages converted to Blade; AdminLTE 3.2 integrated; PWA manifest dynamic via admin settings — ✅
   - Input masks + CEP autofill + password strength implemented in layout scripts — ✅

7) Segurança & Deploy
   - cPanel deploy guide added. Cron and queue guidance included — ⚠️ need to enable on server

8) Testes
   - Basic seeders and initial data included; add automated tests next — TODO

9) Como rodar o scaffold localmente
   - `composer install`
   - `cp .env.example .env` + configurar DB e keys
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan storage:link`

Próximos passos prioritários:
- Testar integrações MercadoPago / PagSeguro em sandbox e implementar validação de notificações
- Implementar testes automatizados para fluxos críticos (registro, pagamento, webhook)
- Adicionar proteção de rota admin (middleware auth + roles)

Tempo estimado restante para MVP: 2–3 semanas de trabalho (integração e testes).
