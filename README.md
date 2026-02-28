# UNN — Plataforma de Networking

## Novidades Recentes (fev/2026)

- **Menu mobile 100% responsivo:** Navegação aprimorada em smartphones, com abertura/fechamento suave e acessibilidade total.
- **Dashboard com métricas em tempo real:** Contadores de visitas e vendas atualizados automaticamente via websockets (Laravel Echo + Pusher).
- **CMS integrado ao frontend:** Conteudos do CMS (Home, Sobre, Manifesto, Valores e Rodape/Redes Sociais) agora refletem diretamente nas paginas publicas, com fallback para Settings quando vazio.
- **Sistema de Parceiros e Cupons:** Nova plataforma de parcerias com carrossel premium no Marketplace, área de cupons exclusivos para membros adimplentes e **painel de autogestão** para que parceiros cadastrem seus próprios benefícios.
- **Widgets customizados por perfil:** Cada membro vê métricas e atalhos conforme seu plano; admin/superadmin têm visão global consolidada.
- **Gerenciamento de tarefas agendadas (cron) pelo painel:** Superadmin pode criar, ativar/desativar, rodar e monitorar tarefas agendadas sem depender do cron da hospedagem.
- **Logs detalhados de execuções:** Histórico de execuções e falhas disponível para cada tarefa agendada.

Veja instruções detalhadas abaixo para uso dessas funcionalidades.

## Melhorias de Dashboard (fev/2026)

- Dashboards de membro e admin totalmente refatoradas com widgets dinâmicos Blade.
- Exibição de métricas, vendas, comunidade, ranking, etc. conforme permissões e plano do usuário.
- Estrutura pronta para integração com dados em tempo real (websockets/Redis) e cache otimizado.
- Novos componentes Blade em `resources/views/components/widgets/` para fácil expansão e manutenção.
- Código preparado para uso de Redis (requer extensão fileinfo habilitada no PHP para produção).

> **Atenção:** Para usar cache Redis, habilite a extensão `fileinfo` no PHP e instale o Predis (`composer require predis/predis`).

# Visão Geral do Sistema

O UNN é uma plataforma completa de networking, cursos e mentorias, desenvolvida em Laravel 10.

## Funcionalidades Principais

### 1. Gestão de Conteúdo (LMS)
- **Cursos:** Aulas em vídeo, anexos, controle de progresso e certificação.
- **Mentorias:** Agendamento, controle de vagas e venda de sessões.
- **Eventos:** Calendário interativo, venda de ingressos e check-in.

### 2. Networking e Comunidade
- **Feed Social:** Publicações, curtidas e comentários (estilo rede social).
- **Conexões:** Sistema de solicitação/aceite de conexões entre membros.
- **Chat:** Mensagens em tempo real (polling otimizado para cPanel).
- **Ranking:** Gamificação baseada em avaliações e interações.

### 3. Administrativo
- **Níveis de Acesso:** Granularidade total (SuperAdmin, Admin, Editor, Instrutor, Membro).
- **Relatórios:** Dashboards financeiros, de vendas e de engajamento.
- **Configurações:** Controle total da plataforma via painel (cores, imagens, textos, integrações).

### 4. Novidades 2026
- **Menu Mobile Responsivo:** Menu principal funcional em todas as telas e dispositivos.
- **Dashboard Dinâmica:** Widgets e métricas em tempo real, segmentados por perfil.
- **Cron Interno:** Gerencie tarefas agendadas direto pelo painel admin (menu "Cron").
- **Logs de Execução:** Visualize histórico de execuções e falhas de cada tarefa agendada.


## Instalação e Deploy

## UTF-8 sem BOM (OBRIGATÓRIO)

- Este projeto usa **UTF-8 sem BOM** em TODOS os arquivos de texto (PHP, Blade, JS, CSS, JSON, MD, etc.).
- Nunca salve arquivos como **UTF-8 com BOM** (bytes `EF BB BF` no início do arquivo), pois causa erros de acentuação/pontuação.
- Antes de commitar, rode: `php tools/check-no-bom.php`.

### Requisitos
- PHP 8.1+
- MySQL 5.7+ / Mariadb
- Composer 2+

### Instruções Rápidas (cPanel/Compartilhado)
1. Configure o banco de dados e o arquivo `.env`.
2. Execute as migrações: `php artisan migrate --seed`.
3. Configure o cron job para rodar `php artisan schedule:run` a cada minuto.
4. Para filas, use `QUEUE_CONNECTION=database` e configure o worker.

#### Para usar o cron interno (painel):
- Acesse o menu **Admin > Cron**
- Cadastre comandos Artisan (ex: `schedule:run`, `queue:work`, etc.) e defina a frequência (cron/preset)
- Ative/desative tarefas conforme necessário
- Execute manualmente e visualize logs de cada execução

#### Para dashboards em tempo real:
- Certifique-se de que as variáveis PUSHER estão configuradas no `.env`
- O painel usará websockets para atualizar contadores automaticamente

#### Menu mobile:
- O menu principal está 100% funcional em smartphones e tablets, com navegação fluida e acessível

### Webhooks de Pagamento
Configure as URLs no seu gateway (MercadoPago/PagSeguro):
- `YOUR_DOMAIN/api/v1/webhooks/mercadopago`
- `YOUR_DOMAIN/api/v1/webhooks/pagseguro`

### SMTP e Emails
Configure as credenciais no painel admin em **Configurações > SMTP**. Use a ferramenta de "Teste de Envio" para validar.

---

## 📅 Histórico de Atualizações

### 07/02/2026 — Webhooks, Logs e Refatoração
- **Configurações Refatoradas:**
  - Divisão da página gigante de configurações em abas modulares (Geral, PWA, SMTP, Gateway, etc.).
- **Logs de Atividade:**
  - Nova interface com **DataTables** para auditoria de ações no sistema.
- **Webhooks:**
  - Implementação robusta para MercadoPago e PagSeguro com processamento automático de matrículas.

### 08/02/2026 — Certificados e Segurança
- **Certificados 2.0:**
  - Novo **Preview HTML** real no modal (substituindo iFrame de PDF) para visualização fiel antes da emissão.
  - Sistema unificado para Cursos, Mentorias e Eventos.
  - Editor visual "Drag-and-drop" para posicionamento de elementos.
- **Proteção de Vídeo:**
  - Bloqueio reforçado contra downloads (nodownload, atalhos de teclado, menu de contexto) no player de vídeo.

### 09/02/2026 — Refinamento de UI/UX e Configurações
- **Admin UI:**
  - **Limpeza Visual:** Remoção de alertas redundantes em todas as telas administrativas, centralizando notificações no sistema `Toastr`.
  - **Toggles:** Correção na persistência de configurações booleanas (PWA, Manutenção, etc.) que não desativavam corretamente.
- **Cursos:**
  - Switch "Habilitar Certificado" movido para a barra lateral para acesso rápido.
  - Refinamento visual na listagem de aulas (alinhamento e remoção de artefatos).

### 10/02/2026 — Ajustes de Email, Faturas e Home
- **Home Page:** 
  - Correção na lógica dos contadores de comunidade.
  - "Empresários de Sucesso" agora contabiliza automaticamente usuários com cargo de **Mentor**.
  - "Iniciantes" reflete membros sem cargos administrativos.
- **Sistema de Emails:**
  - Implementação de **Rate Limiting** (100 envios/hora) para proteção de IP/SMTP.
  - Correção na fila de envios (`SendInvoiceEmailJob`) para compatibilidade com drivers de fila padrão.
  - Diagnóstico e correção de configurações SMTP no `.env`.
- **Faturas (Invoices):**
  - **Redesign Completo:** Novo layout HTML/CSS para PDFs, com inserção confiável de logotipo (Base64).
  - **Correção Geral:** Resolvido erro 500 na edição/criação de faturas (`Undefined variable $rows`).
  - **Emails:** Template de notificação de fatura modernizado e alinhado com a identidade visual.

### 10/02/2026 (Tarde) — Conclusão de Cursos e Certificação
- **Certificados Automáticos:**
  - Geração automática ao atingir **89% de progresso** do curso.
  - Cálculo dinâmico da carga horária real assistida (armazenada no banco).
  - Coluna `workload` adicionada à tabela de certificados.
- **Botão "Concluir Curso":**
  - Exibição condicional: só aparece quando o aluno atinge 89% de conclusão.
  - Validação backend robusta (impede bypass da regra dos 89%).
  - Confirmação elegante via **SweetAlert2** (substituindo alert nativo do navegador).
  - Redirecionamento para o Dashboard do aluno após conclusão.
- **Enrollment Polimórfico:**
  - Correção no registro de conclusão usando `enrollable_id` e `enrollable_type`.
  - Atualização de status para "completed" ao finalizar curso.

### 17/02/2026 — Migração do Painel Admin (Fases 3 e 4)
- **Migração Completa de Módulos:**
  - 12+ módulos administrativos migrados para Tailwind CSS e novos controllers em `Panel\Admin`.
- **Módulos de Conteúdo:**
  - **Cursos:** CRUD completo com gestão de aulas via modal e reordenamento drag-and-drop.
  - **Mentorias:** Form de edição por abas para agenda, preços e links dinâmicos.
  - **Eventos:** Integração com **FullCalendar 6** para exibição dinâmica de agenda.
  - **Certificados:** Painel de gestão de emissão direta para alunos pendentes.
- **CMS e Engajamento:**
  - **FAQ:** Gestão aprimorada com editor Summernote.
  - **Depoimentos:** Fluxo de moderação (Aprovar/Recusar) para testemunhos de usuários.
  - **CMS:** Novo editor de conteúdo do site (Home, Sobre, Rodapé) com abas e preview.
  - **Gamificação:** Gestão de Regras de Pontos e **Ranking Geral** com pódio visual.
- **UX e Navegação:**
  - **Novo Dashboard Admin:** Central premium com cards de acesso rápido para todos os módulos.
  - **Sidebar Reorganizada:** Categorias lógicas (**Gestão, Conteúdo, Ajustes**) para melhor fluxo de trabalho.
  - **Correção de Paginação:** Substituição de `withQueryString` legado por `appends()` para estabilidade em servidores cPanel.

### 17/02/2026 (Tarde) — Refatoração de Templates de E-mail (Fase 5)
- **Editor em Modal:** Migração da edição de templates de e-mail para um fluxo de modal moderno via AJAX.
- **Integração Summernote:** Editor de texto rico integrado especificamente para garantir compatibilidade HTML nos e-mails.
- **Variáveis Dinâmicas:** Nova interface para inserção de variáveis com um clique, categorizadas por ícones.
- **Visualização Completa:** Sistema de pré-visualização que ajusta a altura dinamicamente para exibir o modelo de e-mail por completo, sem cortes.
- **Teste Rápido:** Atalho para envio de e-mail de teste diretamente do modal de edição.

### 17/02/2026 (Noite) — Correção de Bloqueio de Cliques Mobile
- **Auditoria Completa:** Identificação e correção de todos os overlays que bloqueavam cliques no mobile.
- **Navbar Mobile:** Removido `pointer-events-none` do container principal do menu mobile.
- **Member Layout:** Removido `pointer-events-none` INLINE da classe do overlay (BUG CRÍTICO).
- **Floating Chat:** Adicionado `pointer-events-none` no container que cobria a tela inteira no mobile (BUG CRÍTICO 2).
- **JavaScript Completo:** Adicionado toggle para `pointer-events` no menu mobile de membros.
- **Cache-Busting v=3:** Atualização de versão do FilePond para forçar reload de assets.
- **Rota de Assinatura:** Adicionada rota POST `subscription.process` faltante.
- **Commits:** `1e1526e`, `765f11a`, `e9ad637`, `e211fe2`, `ca8f434`, `0c1fe6b`, `003ffca` (CHAT)

### 20/02/2026 — Centralização de Assets e Padronização de UI
- **Centralização de Assets:**
  - Nova lógica centralizada no model `Setting::getUrl()` para resolução de caminhos de arquivos (storage, uploads e externos).
  - Padronização de logos em todo o sistema: **Site, Painel Administrativo, E-mails e Certificados**.
  - Correção de erros de variável `$getUrl` indefinida em múltiplos partials de configurações.
- **Conformidade SweetAlert2:**
  - Remoção definitiva de alertas `alert()` nativos nas configurações de Gateways.
  - Implementação de diálogos elegantes de sucesso/erro via **SweetAlert2** para uma experiência premium.
- **Correção de Erros:**
  - Solucionado Erro 500 no formulário de cursos ao acessar o preview de certificado (Rota faltante implementada).
- **Refatoração de Controllers:**
  - Simplificação do `SettingController` delegando a resolução de URLs para o Model, aumentando a manutenibilidade.

### 27/02/2026 — Integração MercadoPago e Pagamentos Síncronos
- **Gateway Checkout:**
  - Resolução definitiva de conflitos de CSS com o Container Brick do MercadoPago.
  - Implementação de Identificação de Integrador (`Integrator ID`) e Plataforma (`Platform ID`) para rastreabilidade de transações de parceiros.
- **Processamento Síncrono (Webhooks Automáticos):**
  - Refatoração da lógica do Webhook de pagamento. Operações como comissões de afiliados, split de vendas e liberação de acesso a cursos agora ocorrem de forma **Síncrona** logo após o banco processar o limite do cartão.
  - O aluno recebe acesso imediato no exato milissegundo em que a tela de Checkout é processada com sucesso (`approved/PAID`), evitando qualquer espera (comum com a assincronicidade dos webhooks padrão).
- **Simulador de Pagamentos e Pix:**
  - Inserção de fluxos independentes de Pix no Checkout (bypass no Brick).
  - Modo Simulador implementado para facilitar testes de homologação/ambiente de desenvolvimento quando chaves de Gateway não estão presentes.

### 27/02/2026 — Split de Pagamento Marketplace
- **Divisão Automatizada de Vendas:**
  - Implementação de sistema de rateio (Split) para vendas de membros.
  - Divisão configurável entre: **Vendedor, Plataforma, Tráfego Pago e Superadmin**.
  - Validação de integridade: a soma das porcentagens é validada no admin para garantir exatamente 100%.
- **Gestão Financeira, Transparência e Auditoria:**
  - Nova tabela `order_splits` para auditoria e rastreamento de cada centavo distribuído.
  - **Extratos de Rateio**:
    - Visualização global para o Superadmin (AdminLTE) com filtros por pedido e recebedor.
    - **Liquidação Manual**: Botão para confirmar o repasse via PIX diretamente no extrato do AdminLTE, com confirmação SweetAlert2.
    - Visualização pessoal para Membros (Novo Painel) para acompanhamento de ganhos reais.
  - Campo **Chave PIX** adicionado e validado no perfil do usuário em ambos os painéis (Bootstrap & Tailwind).
- **Webhook Inteligente:**
  - O processamento de pagamentos agora dispara automaticamente o cálculo do split no momento da aprovação da ordem.
- **Interface e Navegação:**
  - Injeção dinâmica de links de extrato nos menus laterais de ambos os sistemas.
  - Correção de bugs de sintaxe e visibilidade no formulário de perfil do novo painel.
  - **Refinamento de UI**: Ajuste do botão de fechar nos modais de conteúdo premium (Cursos e Mentorias) para garantir um círculo perfeito e geometria simétrica, removendo bordas/fundos estáticos indesejados.

### 25/02/2026 — Padronização de UX, Sistema de Parceiros e Expansão
- **Monitoramento de Saúde (Dashboards):**
  - **Saúde da Comunidade (Admin):** Integração de seção visual no novo painel administrativo com indicadores de engajamento do ecossistema.
  - **Saúde do Membro (Pessoal):** Widget interativo introduzido no dashboard do membro, apresentando checklist de visibilidade e score de perfil.
- **Sistema de Parceiros 1.0 (Expansão):**
  - **Injeção Global:** O carrossel de parceiros agora é exibido automaticamente em todas as páginas públicas do frontend.
  - **Integração no Painel:** Atalho direto para gestão de parcerias adicionado ao novo dashboard administrativo moderno.
  - Área de cupons para membros com Clipboard API e restrição de acesso por plano.
  - Painel de autogestão corporativa para parceiros autorizados regatarem e gerenciarem seus próprios benefícios.
- **Tooltips Premium:**
  - Injeção global de tooltips personalizadas (Azul UNN) em todos os botões das áreas Admin, Member e Superadmin.
  - Motor reativo para suporte a navegação PJAX (reinicialização automática de tooltips).
- **Notificações Toastr:**
  - Padronização definitiva de retornos AJAX e sessões via **Toastr**.
  - Remoção de SweetAlert2 Toasts redundantes para uma interface mais limpa e focada.

### 25/02/2026 (Tarde) — Refinamento Técnico MercadoPago (MCP)
- **Configuração Dinâmica:**
  - MercadoPago agora lê credenciais e configurações diretamente do banco de dados, ignorando cache estático em modo CLI.
  - Implementação essencial para correta execução de **Tarefas Agendadas (Cron)** e do **Servidor MCP**.
- **Identificação da Plataforma:**
  - Adicionados campos para **Integrator ID** e **Platform ID** na interface administrativa do Gateway.
  - Injeção automática de headers de identificação em transações via cartão de crédito para rastreamento de qualidade.
- **MCP Server:**
  - Comando `php artisan mcp:configure` aprimorado para exibir status completo da integração.

---
© 2026 UNN Networking. Todos os direitos reservados.
