# Spec: Divisão de Pagamento e Comissão

## Descrição
Lógica de processamento de pagamentos que garante que a plataforma receba sua comissão por cada venda realizada por instrutores terceiros.

## Requisitos
- **Taxa de Comissão**: Definida globalmente pelo SuperAdmin (ex: 10% por venda).
- **Split Automático**: Usar as APIs de Marketplace do MercadoPago ou PagSeguro para dividir o valor no checkout.
- **Auditoria**: Tabela `commissions_log` para registrar ID da venda, valor total, comissão da plataforma e valor líquido do instrutor.
- **Relatório**: Área no Admin para visualizar o faturamento total em comissões.

## Regras de Negócio
- A comissão deve ser descontada no momento da confirmação do pagamento (`status == 'paid'`).
- A plataforma não acumula dívidas para o instrutor; a taxa é transacional (paga por venda).

## Integração
- Service: `App\Services\PaymentService` atualizado para tratar o parâmetro `application_fee` ou `commission`.
- Webhook: Atualização da lógica de confirmação para registrar a entrada na conta da plataforma.
