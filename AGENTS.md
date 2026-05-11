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

## Atualização do CHANGELOG

- **SEMPRE** atualizar o `CHANGELOG.md` ao finalizar uma feature ou correção significativa.
- Incluir: data, o que foi adicionado/corrigido, arquivos principais afetados.
- Manter a seção "Decisões Técnicas Importantes" atualizada se houver mudança de stack/padrão.
