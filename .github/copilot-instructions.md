# Instruções para agentes (Copilot)

Plataforma de cursos/eventos online Laravel. UI em português brasileiro.

## Estilo de código
- **PSR-12** para PHP. Controllers em `app/Http/Controllers/`, Models em `app/Models/`.
- Models usam `$fillable` (nunca `$guarded`), `$casts` para tipos, traits customizados (`HasRoles`, `HasFeatureAccess`).
- Validação inline: `$request->validate([...])` com mensagens em pt-BR.
- Exemplos: consulte os Models e Controllers reais do projeto.

## Arquitetura
- **Monolito Laravel** com service layer em `app/Services/` (PaymentService, CouponService, InvoiceService).
- **Admin panel**: AdminLTE 3.2 em `resources/views/admin/`, rotas prefixadas `/admin` com `AdminMiddleware`.
- **Frontend público**: Tailwind CSS (CDN). Admin usa Tailwind + AdminLTE (Bootstrap 4).
- **Roles**: coluna `role` no User (`member`, `admin`, `superadmin`) + traits RBAC customizados.
- **Settings**: tabela key-value acessada via `Setting::get('key')`.

## Build & Test
```bash
composer install
copy .env.example .env   # Windows (ou cp no Unix)
php artisan key:generate
php artisan serve
./vendor/bin/phpunit     # ou: php artisan test
```
⚠️ Migrations tocam DB real — só rode `php artisan migrate` com ambiente configurado.

## Convenções do projeto
- **Routes**: web em pt-BR (`/cursos`, `/eventos`), API em `/api/v1/` protegida por Sanctum.
- **Middleware**: `check.feature:community`, `check.plan`, `AdminMiddleware` para controle de acesso.
- **Jobs/Mail**: `app/Jobs/` com `ShouldQueue`, `app/Mail/` com attachments PDF.
- **Uploads**: `storage/uploads/`, `public/uploads/` via `config/filesystems.php`.
- **Slugs**: auto-gerados com sufixo `uniqid()` no boot do model.

## Integrações
- **Pagamentos**: MercadoPago/PagSeguro via `app/Services/MercadoPagoService.php`, config em `.env`.
- **Model GatewayAccount**: tokens encriptados (`'encrypted'` cast) por vendedor.
- **Webhooks**: `/webhook/mercadopago`, `/webhook/pagseguro`.

## Segurança
- Credenciais em `.env` — nunca commitar.
- Campos sensíveis usam cast `'encrypted'`.
- Verificar `app/Http/Middleware/` ao tocar auth/planos.

## OpenSpec (workflow de mudanças)
Diretório `openspec/` documenta features com specs estruturados. Veja `openspec/changes/` para mudanças ativas.

## Comandos rápidos
```bash
php artisan view:clear           # Limpar cache de views
php artisan route:clear          # Limpar cache de rotas
php artisan config:clear         # Limpar cache de config
./vendor/bin/phpunit --filter=NomeDoTeste  # Rodar teste específico
```
