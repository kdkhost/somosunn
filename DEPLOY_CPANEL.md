# Deploy para cPanel (Shared Hosting)

1. Verifique a versão do PHP no cPanel (deve suportar PHP 8.4).
2. Se possível, use SSH: `composer install`, `php artisan migrate --force`, `php artisan storage:link`.
3. Caso não tenha SSH: crie o projeto localmente, rode `composer install`, compacte tudo (incluindo `vendor/`) e envie via FTP/Upload.
4. Configure as variáveis em `.env` via Editor de Arquivos do cPanel.
5. Configure cron para processar filas: `* * * * * php /home/usuario/path/artisan queue:work --once`
6. Configure webhooks (MercadoPago/PagSeguro) apontando para `/webhook/...` no painel do gateway.

Dicas:
- Use storage externo (S3) para arquivos grandes quando possível.
- Em hospedagem compartilhada, use driver de filas `database` e crontab para processar jobs.
