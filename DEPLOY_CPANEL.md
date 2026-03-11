# Deploy para cPanel (Shared Hosting)

## 1) Requisitos e upload
1. Verifique a versão do PHP no cPanel (PHP **8.1+**).
2. Se possível, use SSH: `composer install --no-dev`, `php artisan migrate --force`.
3. Sem SSH: rode `composer install` localmente, compacte o projeto **incluindo `vendor/`** e envie via FTP/Upload.
4. Aponte o domínio para a pasta do projeto (este repo já roteia tudo para `public/index.php` via `.htaccess`).

## 2) Variáveis de ambiente essenciais
No `.env` do servidor (produção):
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://seu-dominio.com`
- `QUEUE_CONNECTION=database`
- `QUEUE_FAILED_DRIVER=database-uuids`

## 3) Filas (Queues) no cPanel (sem Redis/daemon)
Em hospedagem compartilhada, o padrão recomendado é:
- Driver: **database**
- Processamento: **Cron a cada 1 minuto**, drenando a fila e parando quando vazio

Cron Job (exemplo):
`* * * * * /usr/local/bin/php /home/USUARIO/public_html/artisan queue:work --stop-when-empty --sleep=1 --tries=3 >> /home/USUARIO/queue.log 2>&1`

Observações:
- Ajuste o caminho do PHP e do `artisan` conforme sua conta.
- Garanta que as tabelas `jobs` e `failed_jobs` existam (rode migrations).

## 4) Cache de rotas/config (após deploy)
Após atualizar arquivos/migrations em produção, rode:
- `php artisan optimize`

Se você alterou `.env`/providers/config e algo ficou “travado” em cache:
- `php artisan optimize:clear`
- depois `php artisan optimize`

## 5) LiteSpeed / OPcache (performance)
Se o servidor usa LiteSpeed:
- Ative **OPcache** no cPanel (MultiPHP INI Editor) quando disponível.
- Use cache agressivo **apenas para assets** (CSS/JS/IMG). Evite cache de páginas com sessão (`/admin`, `/login`, checkout, etc.).

## 6) Banco de dados (MariaDB)
- Mantenha índices para consultas críticas (ex.: conexões por `status`, listagens e filtros). Este projeto já inclui migrations de índices.

## 7) Gestão de arquivos (crescimento)
- Para não estourar disco no compartilhado, considere mover anexos/vídeos para S3 compatível.
- Laravel suporta isso via Filesystem, mas exige configurar o disco e instalar o driver S3 quando necessário.

## 8) Webhooks
Configure webhooks (MercadoPago) apontando para `/webhook/...` no painel do gateway.
