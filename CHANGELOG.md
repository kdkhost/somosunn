# CHANGELOG - SOMOS UNN

> **IMPORTANTE PARA AGENTES DE IA**: Leia este arquivo E o `AGENTS.md` ANTES de modificar qualquer coisa no projeto. Este changelog documenta todas as funcionalidades implementadas, decisoes tecnicas e padroes adotados.

---

## [2026-05-11] - Modulo Revistas Digitais (Flipbook)

### Adicionado
- **Modulo completo de Revistas Digitais** com visualizador flipbook
- Migration `magazines` (titulo, slug, PDF, capa, categoria, edicao, status, visibility, views_count, soft deletes)
- Model `Magazine` com scope `visibleTo($user)` baseado em interesse "Noticias"
- Controller publico `MagazineController` (listagem + flipbook viewer)
- Controller admin `Admin\MagazineController` (CRUD com upload drag-and-drop)
- **Dois engines de flipbook** alternáveis pelo admin:
  - **DearFlip** (padrao): leve, streaming progressivo, controles nativos
  - **PDF.js + StPageFlip**: renderizacao Mozilla com efeito 3D page-flip
- **Deteccao automatica de spreads**: paginas landscape (aspect > 1.15) sao divididas em 2
- **Carregamento progressivo**: renderiza 4 paginas iniciais, lazy-load restante em background
- **Som de page-flip realista** via Web Audio API (3 fases: woosh + crinkle + thwap)
- **Loading branded**: circulo azul UNN com logo da plataforma no centro
- **Setas laterais customizadas** posicionadas junto ao livro
- **Toolbar inferior** pill-shape com navegacao, som, zoom, download, fullscreen
- **Pagina /revistas** com:
  - Hero section com identidade visual UNN (azul #1F5EDB)
  - Cards com mascara fume semi-transparente configuravel
  - Grid responsivo desktop + Swiper mobile (1 por vez, drag)
  - Filtros (busca + categoria) + paginacao customizada
- **Permissoes**:
  - `magazines.access` - Acessar revistas digitais
  - `magazines.publish` - Publicar revistas (Editor)
  - Superadmin/Admin tem acesso irrestrito
- **Configuracoes no admin** (Settings > Geral > Revistas):
  - Plugin de visualizacao (DearFlip / StPageFlip)
  - Revistas por pagina (padrao 10)
  - Opacidade da mascara dos cards (slider 30-95%)
- **Importacao automatica**: comando `php artisan magazines:import-manchete`
- **14 edicoes da Revista Manchete** importadas e publicadas (patrocinadora)
- **Componente `x-unn-dropzone`** reutilizavel para drag-and-drop em qualquer painel
- Sidebars atualizadas (admin antigo + painel novo) com item "Revistas"

### Corrigido
- Superadmin agora tem acesso irrestrito ao painel novo (`/painel/admin/*`)
  - Removido redirect que bloqueava superadmin no `EnsureUserIsAdmin`
- Upload de PDF: `getSize()` chamado ANTES do `storeUploadedFile` (arquivo tmp era movido)
- Inversao de cores no flipbook: forcado `color-scheme: light` + fundo branco no canvas
- Ultima pagina nao mais se sobrepoe (showCover: true + spread detection)

### Arquivos principais
- `app/Models/Magazine.php`
- `app/Http/Controllers/MagazineController.php`
- `app/Http/Controllers/Admin/MagazineController.php`
- `app/Console/Commands/ImportManchetePdfs.php`
- `resources/views/magazines/index.blade.php` (listagem publica)
- `resources/views/magazines/show.blade.php` (DearFlip viewer)
- `resources/views/magazines/show-stpageflip.blade.php` (PDF.js + StPageFlip viewer)
- `resources/views/admin/magazines/index.blade.php` (admin AdminLTE)
- `resources/views/admin/magazines/form.blade.php` (admin AdminLTE)
- `resources/views/panel/admin/magazines/index.blade.php` (painel novo)
- `resources/views/panel/admin/magazines/form.blade.php` (painel novo)
- `resources/views/components/unn-dropzone.blade.php`
- `public/assets-dflip/` (DearFlip assets locais)
- `database/migrations/2026_05_10_192542_create_magazines_table.php`

---

## [2026-05-10] - Busca de Estabelecimentos (TomTom)

### Adicionado
- **TomTom** como provedor primario de busca de estabelecimentos
- Cascata com fallback: TomTom > Google Places > LocationIQ
- Se busca com bias (lat/lon + raio) retorna vazio, tenta sem bias
- Flag `out_of_radius` na resposta para auto-marcar "fora do raio"
- Campo TomTom API Key no admin (Settings > Geral > APIs de Localizacao)
- Provider select atualizado: "Automatico (TomTom > Google > LocationIQ)"

### Arquivos
- `app/Http/Controllers/Api/VenueSearchController.php`
- `resources/views/admin/settings/partials/general.blade.php`

---

## [2026-05-10] - Correcoes de Permissoes

### Corrigido
- Superadmin tem acesso irrestrito a TODOS os modulos sem excecao
- Middleware `EnsureUserIsAdmin`: superadmin passa direto (sem redirect)
- Middleware `RedirectSuperadminToLegacy`: ja era no-op (confirmado)
- Editors com feature `magazines.publish` podem acessar `panel.admin.magazines.*`

---

## Decisoes Tecnicas Importantes

### Stack do Projeto
- **Backend**: Laravel 10.x, PHP 8.x, MySQL/MariaDB
- **Frontend site + painel novo**: Tailwind CSS (CDN), jQuery 3.6, FilePond, Cropper.js
- **Frontend admin antigo**: AdminLTE 3.2, Bootstrap 4, jQuery
- **Sem framework reativo** (sem Vue, React, Alpine)
- **Deploy**: git push + ssh `git fetch && git reset --hard origin/main`
- **Storage**: local em `public/storage/` (sem S3)

### Padroes a Seguir
- UTF-8 sem BOM (rodar `php tools/check-no-bom.php` antes de commitar)
- Views do admin antigo: `@extends('admin.layouts.app')` com AdminLTE
- Views do painel novo: `@extends('panel.layouts.app')` com Tailwind
- Views publicas: `@extends('layouts.app')` com Tailwind
- Uploads: usar `UploadStorage::storeUploadedFile()` com `watermark => false` para PDFs
- Features/permissoes: definidas em `Plan::FEATURE_LABELS` e `Plan::FEATURE_GROUPS`
- Rotas admin antigo: prefixo `admin.` em `routes/web.php`
- Rotas painel novo: prefixo `panel.admin.` em `routes/web.php`
- Sidebar admin: `resources/views/admin/partials/sidebar.blade.php`
- Sidebar painel: `resources/views/panel/partials/sidebar.blade.php`

### Cores da Plataforma
- Azul principal: `#1F5EDB` (--unn-azul-1)
- Azul secundario: `#177FD6` (--unn-azul-2)
- Azul escuro: `#1D3FC4` (--unn-azul-3)
- Gradiente botoes: `linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2), var(--unn-azul-3))`

### Usuarios Importantes
- Superadmin: Marcelo Brad (id=2, email: marcelobradrj@gmail.com)
- Plataforma: Jorge Orlandi (id=1)
- Marketing Manager: Monique (id=38)
- Split: 70% vendedor / 10% plataforma / 10% marketing / 10% superadmin
