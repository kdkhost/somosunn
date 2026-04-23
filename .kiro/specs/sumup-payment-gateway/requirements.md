# Requisitos — Integração Gateway SumUp

## Introdução

Este documento define os requisitos para integrar o gateway de pagamento **SumUp** como opção adicional ao Mercado Pago já existente na plataforma. A integração deve cobrir todos os tipos de transações do sistema (cursos, eventos, mentorias, marketplace, assinaturas), suportar cartão de crédito e PIX, e incluir painel administrativo completo com relatórios e webhooks dinâmicos.

---

## Requisitos

### 1. Configuração do Gateway

#### 1.1 Credenciais no Painel Admin
- O sistema deve permitir que o superadmin configure as credenciais SumUp (Client ID, Client Secret, API Key) nas configurações globais da plataforma.
- O sistema deve permitir que vendedores/instrutores conectem suas próprias contas SumUp via OAuth ou API Key.
- O sistema deve armazenar as credenciais SumUp no modelo `GatewayAccount` com `provider = 'sumup'`.
- O sistema deve suportar modo sandbox e modo produção, configurável pelo admin.

#### 1.2 Resolução de Credenciais
- O sistema deve resolver credenciais SumUp com a mesma lógica de prioridade do Mercado Pago: credenciais do vendedor têm prioridade sobre as globais da plataforma.
- O sistema deve validar as credenciais antes de salvar, fazendo uma chamada de teste à API SumUp.

**Propriedades de Correção:**
- Para qualquer transação, se o vendedor tiver credenciais SumUp válidas, elas devem ser usadas; caso contrário, as credenciais globais devem ser usadas.
- Credenciais inválidas nunca devem ser salvas sem aviso ao admin.

---

### 2. Checkout Integrado

#### 2.1 Seleção de Gateway no Checkout
- O sistema deve exibir SumUp como opção de pagamento nos checkouts de: cursos, eventos, mentorias, produtos do marketplace e assinaturas de planos.
- O sistema deve exibir SumUp apenas quando houver credenciais válidas configuradas para o contexto da transação.
- O sistema deve manter o Mercado Pago como opção simultânea (não substituir).

#### 2.2 Métodos de Pagamento SumUp
- O sistema deve suportar pagamento via **cartão de crédito** (com tokenização via SumUp.js ou SDK).
- O sistema deve suportar pagamento via **PIX** (geração de QR Code e código copia-e-cola).
- O sistema deve exibir o formulário de cartão de forma integrada na página (sem redirecionamento externo).
- O sistema deve exibir o QR Code PIX inline na página de checkout.

#### 2.3 Tokenização de Cartão
- O sistema deve permitir salvar o cartão do comprador para uso futuro (tokenização via SumUp).
- O sistema deve exibir cartões salvos no checkout para reuso com um clique.
- O comprador deve poder remover cartões salvos do seu perfil.

**Propriedades de Correção:**
- O número completo do cartão nunca deve ser armazenado no banco de dados da plataforma.
- Apenas tokens SumUp devem ser persistidos.
- Um pagamento com cartão tokenizado deve produzir o mesmo resultado que um pagamento com cartão novo.

---

### 3. Processamento de Pagamentos

#### 3.1 Pagamentos Únicos (One-Time)
- O sistema deve processar pagamentos únicos via SumUp para cursos, eventos, mentorias e produtos do marketplace.
- O sistema deve criar um registro `Order` com `gateway = 'sumup'` ao iniciar o pagamento.
- O sistema deve atualizar o status do `Order` para `paid` ao receber confirmação de pagamento.
- O sistema deve executar todos os fulfillments existentes (matrícula em curso, confirmação de ingresso, etc.) após pagamento confirmado.

#### 3.2 Pagamentos Recorrentes / Assinaturas
- O sistema deve criar assinaturas recorrentes via SumUp para planos de assinatura da plataforma.
- O sistema deve armazenar o ID da assinatura SumUp no modelo `Subscription`.
- O sistema deve processar renovações automáticas via webhook SumUp.
- O sistema deve cancelar assinaturas SumUp quando o usuário cancelar o plano na plataforma.

#### 3.3 Reembolsos
- O sistema deve suportar reembolso total de pagamentos SumUp via painel admin.
- O sistema deve suportar reembolso parcial de pagamentos SumUp via painel admin.
- O sistema deve atualizar o status do `Order` para `refunded` após reembolso total confirmado.
- O sistema deve registrar o valor reembolsado nos metadados do `Order`.

**Propriedades de Correção:**
- O valor total reembolsado nunca deve exceder o valor original pago.
- Um reembolso bem-sucedido deve sempre atualizar o status do pedido.
- Reembolsos parciais devem preservar o status `paid` do pedido até que o total seja reembolsado.

#### 3.4 Split de Pagamentos
- O sistema deve suportar divisão de pagamentos SumUp entre a plataforma e vendedores/instrutores.
- O sistema deve calcular splits usando a mesma lógica do `OrderSplit` existente.
- O sistema deve registrar cada split no modelo `OrderSplit` com status de liquidação.

---

### 4. Webhooks Dinâmicos

#### 4.1 Registro de Webhook por Requisição
- O sistema deve registrar um webhook dinâmico na API SumUp a cada nova transação iniciada.
- A URL do webhook deve ser única por transação (ex: `/webhook/sumup/{order_id}/{token}`).
- O sistema deve validar a autenticidade do webhook via assinatura HMAC ou token secreto por transação.
- O sistema deve processar os seguintes eventos de webhook: `payment.succeeded`, `payment.failed`, `payment.refunded`, `checkout.completed`, `subscription.renewed`, `subscription.cancelled`.

#### 4.2 Segurança dos Webhooks
- O sistema deve rejeitar webhooks com assinatura inválida com HTTP 401.
- O sistema deve processar cada webhook de forma idempotente (reprocessamento não deve duplicar ações).
- O sistema deve registrar todos os webhooks recebidos em log de auditoria.

**Propriedades de Correção:**
- Para qualquer webhook recebido, se a assinatura for inválida, o pedido nunca deve ser atualizado.
- Para qualquer webhook `payment.succeeded` recebido duas vezes para o mesmo pedido, o fulfillment deve ser executado apenas uma vez.

---

### 5. Painel Administrativo

#### 5.1 Listagem de Transações SumUp
- O sistema deve exibir uma listagem paginada de todas as transações SumUp no painel admin.
- A listagem deve permitir filtrar por: status, período, método de pagamento, vendedor.
- A listagem deve exibir: ID da transação, comprador, valor, método, status, data.
- O admin deve poder visualizar os detalhes completos de cada transação.

#### 5.2 Gestão de Reembolsos
- O admin deve poder iniciar reembolsos totais e parciais diretamente pelo painel.
- O sistema deve exibir o histórico de reembolsos de cada transação.
- O sistema deve confirmar a ação de reembolso antes de executar.

#### 5.3 Relatórios SumUp
- O sistema deve gerar relatório de vendas SumUp por período (diário, semanal, mensal).
- O sistema deve exibir totais de: receita bruta, taxas SumUp, receita líquida, reembolsos.
- O sistema deve permitir exportar relatórios em CSV.
- O sistema deve exibir gráfico de evolução de vendas SumUp.

#### 5.4 Configurações SumUp no Admin
- O admin deve poder ativar/desativar o SumUp como opção de pagamento globalmente.
- O admin deve poder configurar as taxas SumUp (percentual e fixo) para cálculo de fees.
- O admin deve poder definir se a taxa é repassada ao comprador ou absorvida pela plataforma.
- O admin deve poder testar a conexão com a API SumUp diretamente pelo painel.

---

### 6. Notificações

#### 6.1 E-mails de Confirmação
- O sistema deve enviar e-mail de confirmação de pagamento ao comprador após pagamento SumUp confirmado.
- O sistema deve reutilizar os templates de e-mail existentes do sistema.
- O sistema deve incluir os dados da transação SumUp no e-mail (ID, método, valor).

#### 6.2 Notificações de Falha
- O sistema deve notificar o comprador por e-mail quando um pagamento SumUp falhar.
- O sistema deve notificar o admin quando um webhook SumUp falhar no processamento.

---

### 7. Segurança e Conformidade

#### 7.1 PCI Compliance
- O sistema nunca deve transmitir dados de cartão pelo servidor da plataforma.
- Toda captura de dados de cartão deve ocorrer via SDK/JS do SumUp diretamente no browser.
- O sistema deve usar HTTPS em todas as comunicações com a API SumUp.

#### 7.2 Logs e Auditoria
- O sistema deve registrar todas as chamadas à API SumUp (request/response) em log de auditoria.
- O sistema deve registrar todos os webhooks recebidos com payload completo.
- Os logs devem ser acessíveis pelo admin no painel de logs existente.

**Propriedades de Correção:**
- Nenhuma chamada à API SumUp deve ocorrer sem registro em log.
- Dados sensíveis (API keys, tokens de cartão) nunca devem aparecer nos logs.
