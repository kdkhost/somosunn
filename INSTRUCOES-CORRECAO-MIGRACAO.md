# Instruções para Corrigir Migração SumUp no Servidor

## Problema Identificado

A migração `2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php` está tentando adicionar a coluna `sumup_subscription_id` após a coluna `gateway`, mas essa coluna não existe na tabela `subscriptions`.

## Solução

### Opção 1: Executar o Script Automático (RECOMENDADO)

1. Faça upload do arquivo `fix-migration.sh` para o diretório raiz do projeto no servidor
2. Dê permissão de execução:
   ```bash
   chmod +x fix-migration.sh
   ```
3. Execute o script:
   ```bash
   ./fix-migration.sh
   ```

### Opção 2: Executar Comandos Manualmente

Execute os seguintes comandos no servidor (dentro do diretório `public_html`):

```bash
# 1. Fazer backup do arquivo de migração
cp database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php.bak

# 2. Editar o arquivo de migração
nano database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php
```

**No editor nano:**
- Localize a linha: `$table->string('sumup_subscription_id')->nullable()->after('gateway')->index();`
- Substitua por: `$table->string('sumup_subscription_id')->nullable()->after('next_billing_at')->index();`
- Salve: `Ctrl+O`, `Enter`
- Saia: `Ctrl+X`

```bash
# 3. Fazer rollback da migração que falhou
php artisan migrate:rollback --step=1

# 4. Executar a migração corrigida
php artisan migrate

# 5. Verificar se funcionou
php artisan db:table sum_up_transactions
```

## Resultado Esperado

Após executar os comandos, você deve ver:

```
INFO  Running migrations.

2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table .......... DONE
```

E a tabela `sum_up_transactions` deve estar criada e pronta para uso.

## Próximos Passos

Após a migração ser executada com sucesso:

1. Teste o checkout de eventos com o gateway SumUp ativo
2. Verifique se a transação é salva corretamente no banco de dados
3. Confirme que não há mais erros de "Table doesn't exist"

## Suporte

Se encontrar algum problema, cole a saída completa dos comandos para análise.
