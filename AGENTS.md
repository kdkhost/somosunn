# Instruções para agentes de IA e editores de código (OBRIGATÓRIO)

> **LEIA ANTES DE QUALQUER MODIFICAÇÃO**: Consulte o `CHANGELOG.md` na raiz do projeto para entender o histórico de funcionalidades, decisões técnicas, padrões de código e arquitetura do sistema. Não duplique funcionalidades já existentes.

---

## Leitura obrigatória antes de modificar

1. **`CHANGELOG.md`** — Histórico completo de features, correções, decisões técnicas, stack, cores, padrões
2. **`AGENTS.md`** (este arquivo) — Regras de codificação e deploy
3. **Verificar arquivos existentes** antes de criar novos (evitar duplicação)

---

## UTF-8 sem BOM (regra absoluta)

- Este projeto usa **UTF-8 sem BOM** em TODOS os arquivos de texto (PHP, Blade, JS, CSS, JSON, MD, etc.).
- É proibido salvar arquivos como **UTF-8 com BOM**.
- Nunca introduza BOM (bytes `EF BB BF`) no início de nenhum arquivo.
- Se o seu editor tiver a opção, selecione **"UTF-8" / "UTF-8 (sem BOM)"** (não use "UTF-8 with BOM").
- Motivo: BOM causa erro de acentuação e pontuação no sistema.

## Checagem antes de commitar

- Rode: `php tools/check-no-bom.php`
- Se houver BOM, re-salve os arquivos como UTF-8 sem BOM e repita a checagem.

## Deploy

- **Nunca** faça push direto para `main` sem verificar sintaxe PHP (`php -l`) e BOM.
- Após push: `ssh -i ~/.ssh/deploy_key -p 1979 somosunn@somosunn.com.br "rm -f /home/somosunn/public_html/.git/index.lock && cd /home/somosunn/public_html && git fetch origin && git reset --hard origin/main && php artisan view:clear && php artisan route:clear"`

## Padrões de código

- **Views admin antigo** (AdminLTE): `@extends('admin.layouts.app')`
- **Views painel novo** (Tailwind): `@extends('panel.layouts.app')`
- **Views públicas** (Tailwind): `@extends('layouts.app')`
- **Uploads**: usar `UploadStorage::storeUploadedFile()` — nunca mover arquivos manualmente
- **Features/permissões**: definir em `Plan::FEATURE_LABELS` e `Plan::FEATURE_GROUPS` (app/Models/Plan.php)
- **Cores da plataforma**: azul `#1F5EDB`, `#177FD6`, `#1D3FC4` (variáveis CSS `--unn-azul-1/2/3`)
- **Sem Vue/React/Alpine** — usar jQuery + Vanilla JS
- **FilePond** para uploads no painel novo (class="filepond" auto-inicializa)
- **Componente `x-unn-dropzone`** para uploads no admin antigo

## Regra permanente: dois painéis administrativos

O SOMOS UNN utiliza dois painéis administrativos distintos e integrados:

1. **Painel antigo/principal**: rota base `/admin`, layout AdminLTE, gestão operacional e global.
2. **Painel novo/moderno**: rota base `/painel`, layout Tailwind, área do usuário, membro, administrador e áreas específicas de conta.

Toda implementação, correção, refatoração, migration, controller, view, rota, menu, permissão, middleware, policy, service e teste deve considerar os dois painéis antes de ser finalizada.

Regras obrigatórias:

- Nunca implementar funcionalidade administrativa somente em `/admin` ou somente em `/painel/admin` sem analisar e documentar se ela deve existir nos dois fluxos.
- Preservar todas as rotas existentes de `/admin` e `/painel`.
- Não redirecionar usuários de `/admin` para `/painel`, nem de `/painel` para `/admin`, sem necessidade funcional explícita.
- Não duplicar lógica de negócio entre painéis; controllers dos dois painéis devem usar o mesmo service layer, model, request, rule, policy ou repositório quando aplicável.
- As views podem ser diferentes, mas a regra de negócio deve ser única.
- Permissões devem funcionar nos dois painéis.
- Administradores podem acessar funções globais nos dois painéis quando essa já for a regra existente.
- Membros não podem acessar rotas administrativas em `/admin` e não podem acessar recursos administrativos em `/painel` sem permissão explícita.
- Rotas novas devem possuir name prefix claro, sem conflito e compatível com o painel correspondente.

Eventos e cupons:

- Neste checkout, as rotas legadas administrativas de eventos usam `/admin/events` e `/painel/admin/events`; não trocar para `/admin/eventos` sem criar compatibilidade ou redirecionamento seguro.
- Cupons de eventos devem existir nos dois painéis quando a gestão administrativa existir nos dois fluxos.
- Padrão atual de names:
  - `admin.events.*`
  - `admin.events.coupons.*`
  - `panel.admin.events.*`
  - `panel.admin.events.coupons.*`
- Padrão atual de controllers:
  - `app/Http/Controllers/Admin/EventCouponController.php`
  - `app/Http/Controllers/Panel/Admin/EventCouponController.php`
- Quando for seguro, o controller do painel novo deve reutilizar ou estender a lógica do controller principal. Se herança não for segura por diferenças de layout, middleware ou fluxo, criar service compartilhado.
- Nunca usar view de `/admin` dentro de `/painel` sem confirmar compatibilidade de layout, seções, componentes, scripts e sidebar. A regra inversa também é proibida sem validação.

Menus e permissões:

- No `/admin`, adicionar itens na sidebar administrativa existente, respeitando permissões e sem duplicar item.
- No `/painel`, adicionar itens na sidebar, navegação ou cards administrativos existentes, respeitando permissões e sem mostrar menu para usuário sem acesso.
- Permissões de cupons e link do grupo de evento devem continuar usando o namespace granular `admin.events.*`, inclusive quando consumidas pelo painel novo.

Matriz de implementação:

- Funcionalidade global administrativa: implementar em `/admin` e `/painel/admin`.
- Funcionalidade do membro: implementar em `/painel`.
- Funcionalidade pública: implementar fora dos painéis, usando rotas públicas.
- Funcionalidade exclusiva de superadmin: implementar em `/admin` e, se já houver equivalência administrativa, também em `/painel/admin`.
- Funcionalidade de patrocinador: implementar em `/painel/patrocinador` e criar equivalência de gestão global em `/admin/patrocinadores` e/ou `/painel/admin/patrocinadores` quando aplicável.

Antes de finalizar alteração administrativa, executar `php artisan route:list` e validar que as rotas equivalentes dos dois painéis continuam presentes. Não remover rotas legadas nem renomear route names sem buscar referências em controllers, Blade, JavaScript, testes, services, notifications, jobs, commands, e-mails e documentação.

Ao alterar recursos administrativos compartilhados, atualizar `RELATORIO_AUDITORIA_E_CORRECOES_SOMOSUNN.md` na seção **Painéis Administrativos** com: recursos existentes em cada painel, recursos compartilhados, rotas preservadas/adicionadas, controllers reutilizados, services compartilhados, testes/validações, permissões e diferenças visuais mantidas.

## Atualização do CHANGELOG

- **SEMPRE** atualizar o `CHANGELOG.md` ao finalizar uma feature ou correção significativa.
- Incluir: data, o que foi adicionado/corrigido, arquivos principais afetados.
- Manter a seção "Decisões Técnicas Importantes" atualizada se houver mudança de stack/padrão.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, invoke the `skill` tool with `skill: "graphify"` before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
