# UNN — Plataforma de Networking

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

### Webhooks de Pagamento
Configure as URLs no seu gateway (MercadoPago/PagSeguro):
- `YOUR_DOMAIN/api/v1/webhooks/mercadopago`
- `YOUR_DOMAIN/api/v1/webhooks/pagseguro`

### SMTP e Emails
Configure as credenciais no painel admin em **Configurações > SMTP**. Use a ferramenta de "Teste de Envio" para validar.

---


## 📅 Histórico de Atualizações

### 14/02/2026 — Novo Painel de Membros, Mobile e UX
- **Novo Painel Moderno:**
  - Todos os módulos administrativos migrados para painel unificado e responsivo (cursos, eventos, certificados, cupons, marketplace).
  - Visual moderno com Tailwind, navegação simplificada e experiência consistente para todos (exceto superadmin).
- **Menu Mobile:**
  - Correção completa do menu mobile, com navegação fluida e acessível em todos os dispositivos.
- **Perfil do Membro:**
  - Novo formulário de perfil com auto-preenchimento de endereço via ViaCEP.
  - Máscaras e limites para telefone e CPF/CNPJ.
  - Validação inline e feedback visual amigável.
- **Certificados e Cupons:**
  - Listagem, download e visualização de certificados no painel.
  - CRUD completo de cupons com validação e feedback.
- **Cursos e Eventos:**
  - CRUD completo, upload de imagens, status e descrições.
- **Marketplace:**
  - Resumo financeiro, vendas, pagamentos e taxas em cards visuais.
- **Padronização:**
  - Todas as views migradas para Blade moderno, seguindo layout único.
  - Controllers e rotas padronizados, seguindo PSR-12 e boas práticas Laravel.
- **Próximos passos:**
  - Drag-and-drop para uploads, controle de tipos de arquivos, ícones de extensão de material.


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


---
© 2026 UNN Networking. Todos os direitos reservados.
