# UNN — Plataforma de Networking (Scaffold)

Este diretório contém o scaffold inicial para um projeto Laravel 12 preparado para PHP 8.4 e hospedagem em cPanel.

Principais instruções rápidas:

1. Copie o conteúdo deste diretório para o servidor (ou use `composer create-project`):
   - `composer install`
   - `cp .env.example .env` e configure as variáveis (DB, pagamentos, social)
   - Ajuste `config/services.php` com as redirect URLs para Socialite
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan storage:link`

PHPMailer
- Instale `composer require phpmailer/phpmailer` (already listed in composer.json)
- Configure `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` in .env
- Acesse `/admin/mail-test` para enviar um e-mail de teste

Payments (MercadoPago / PagSeguro)
- Configure as credenciais no `.env` conforme `MERCADOPAGO_ACCESS_TOKEN`, `PAGSEGURO_TOKEN`
- Webhooks endpoints:
  - /webhook/mercadopago
  - /webhook/pagseguro

PWA
- Ative/desative pelo painel administrativo em Configurações → PWA
- Upload de ícones no painel atualiza `manifest.webmanifest` dinamicamente

Instalador Web
- Acesse `/install` em ambiente sem `APP_INSTALLED=true` para executar migrations, seeders e criar o administrador inicial via formulário web.
- O instalador gerará `APP_KEY`, rodará `php artisan migrate --seed` e adicionará `APP_INSTALLED=true` no `.env` (se permitido).

Uploads
- Teste de upload chunked disponível em `/admin/upload-test`
- Configurações de formatos e limites em `config/uploads.php`
2. Para hospedagem em cPanel sem SSH, gere o projeto localmente e suba os arquivos (incluindo `vendor/`).

3. Gateways de pagamento configurados por placeholders: MercadoPago e PagSeguro.

4. Frontend inicial convertido para Blade em `resources/views/site` (você pode ajustar e adicionar assets em `public/`).

Recursos incluídos no scaffold:
- Rotas básicas
- Controller `HomeController` com views: `index`, `portal`, `premium`
- PWA manifest e service-worker placeholders
- Arquivos de autenticação (esqueleto) e página administrativa a integrar (AdminLTE)

Próximo passo sugerido: rodar `composer install` e executar o `artisan` para validar o scaffold.

## Instalação resiliente

- O `.htaccess` de raiz do repositório serve o `index.html` mesmo se o banco não estiver acessível; as requisições que usam `backend` são encaminhadas para `backend/public`.
- O `.htaccess` dentro de `backend/public` garante que futuras rotas Laravel caiam no front controller (mesmo que esse front controller ainda esteja sendo finalizado).
- Enquanto o banco estiver offline, o layout público (e o painel admin) exibem um aviso em amarelo, e o `AppServiceProvider` registra `Banco de dados indisponível` nos logs.
- Depois que o banco estiver disponível, rode `php artisan migrate --force` e `php artisan db:seed` para restaurar os dados e habilitar a interface administrativa.

## Próximos passos recomendados

1. Conferir se `backend/public/storage` e `storage/logs` existem e têm permissão de escrita; copie `public/service-worker.js`, `public/manifest.webmanifest` e demais assets para o servidor.
2. Atualizar `.env` com `APP_URL`, credenciais de pagamentos e as variáveis do PHPMailer.
3. Testar rotas de webhook e o instalador (`/install`) antes de abrir o admin.

## Novas funcionalidades do portal

- Os dados de eventos gratuitos, mentorias pagas e ranking agora são carregados do backend Laravel (`HomeController`) e podem ser acessados publicamente mesmo se o banco estiver no modo leitura (avisos aparecem nas views públicas). Os templates Blade nas pastas `resources/views/site` exibem cards dinâmicos com essas informações.
- Endpoints JSON expõem o fluxo de networking:
  - `POST /api/interactions` recebe `user_from_id`, `user_to_id`, `message` e meta opcional para registrar conexões dentro do mesmo nível.
  - `POST /api/satisfactions` grava a pesquisa de satisfação com `interaction_id`, `rating` (1-5) e comentário, atualizando automaticamente o ranking.
  - `GET /api/ranking` mostra os primeiros colocados e o resumo por nível para alimentar dashboards ou widgets externos.
- O serviço `App\Services\RankingService` recalcula média, contagem e score baseado nos feedbacks e armazena em `rankings`, garantindo que o ranking do portal sempre reflita o histórico real das conexões.
