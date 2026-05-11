# CHANGELOG - SOMOS UNN

---

## [2026-05-11] - Modulo Revistas Digitais (Flipbook)

### Adicionado
- Modulo completo de Revistas Digitais com visualizador flipbook
- Migration `magazines` (titulo, slug, PDF, capa, categoria, edicao, status, visibility, views_count, soft deletes)
- Model `Magazine` com scope `visibleTo($user)` baseado em interesse "Noticias"
- Controller publico (listagem + flipbook viewer)
- Controller admin (CRUD com upload drag-and-drop)
- Dois engines de flipbook alternaveis pelo admin:
  - DearFlip (padrao): leve, streaming progressivo, controles nativos
  - PDF.js + StPageFlip: renderizacao Mozilla com efeito 3D page-flip
- Deteccao automatica de spreads: paginas landscape (aspect > 1.15) divididas em 2
- Carregamento progressivo: renderiza 4 paginas iniciais, lazy-load restante
- Som de page-flip realista via Web Audio API
- Loading branded com logo da plataforma no centro do circulo
- Setas laterais customizadas posicionadas junto ao livro
- Toolbar inferior com navegacao, som, zoom, download, fullscreen
- Pagina /revistas com hero, cards com mascara fume, grid responsivo, Swiper mobile
- Permissoes: magazines.access, magazines.publish
- Configuracoes no admin (plugin, revistas por pagina, opacidade mascara)
- Comando `php artisan magazines:import-manchete` para importar edicoes
- 14 edicoes da Revista Manchete importadas e publicadas
- Componente `x-unn-dropzone` reutilizavel para drag-and-drop

### Corrigido
- Superadmin com acesso irrestrito ao painel novo
- Upload de PDF: getSize() antes do storeUploadedFile
- Inversao de cores no flipbook: forcado color-scheme light
- Ultima pagina nao mais se sobrepoe (showCover + spread detection)

---

## [2026-05-11] - Cron Autonomo

### Corrigido
- Tarefas do banco agora atualizam `last_run_at` automaticamente quando executadas pelo scheduler
- Registra log automatico em `scheduled_task_logs` apos cada execucao
- Botao "Executar Todas" adicionado na pagina de cron (ambos paineis)

---

## [2026-05-10] - Busca de Estabelecimentos (TomTom)

### Adicionado
- TomTom como provedor primario de busca de estabelecimentos
- Cascata com fallback: TomTom > Google Places > LocationIQ
- Se busca com bias retorna vazio, tenta sem bias
- Flag `out_of_radius` na resposta
- Campo TomTom API Key no admin

---

## Stack Tecnico

- Backend: Laravel 10.x, PHP 8.x, MySQL/MariaDB
- Frontend site + painel novo: Tailwind CSS (CDN), jQuery 3.6, FilePond, Cropper.js
- Frontend admin antigo: AdminLTE 3.2, Bootstrap 4, jQuery
- Deploy: git push + ssh git fetch && git reset --hard origin/main
- Storage: local em public/storage/

## Cores da Plataforma

- Azul principal: #1F5EDB (--unn-azul-1)
- Azul secundario: #177FD6 (--unn-azul-2)
- Azul escuro: #1D3FC4 (--unn-azul-3)
