# Proposta: Sistema Administrativo Total e Marketplace de Instrutores

## Objetivo
Expandir o painel administrativo para controle total do frontend e implementar um sistema de marketplace onde membros (instrutores) podem vender cursos e mentorias, utilizando seus próprios gateways de pagamento e pagando comissão à plataforma.

## Motivação
Atualmente, o conteúdo do frontend é estático ou depende de seeders/banco de dados via código. Além disso, a plataforma não permite que membros ajam como vendedores independentes, limitando o potencial de escala e monetização (comissão por venda).

## O que vai mudar
- **CMS Administrativo**: Criação de telas para gerenciar seções da Landing Page, Páginas Institucionais e Banners.
- **Vendor Dashboard**: Uma nova área no painel para membros com planos "Completo/VIP" para gerenciar seus próprios produtos.
- **Split de Pagamento/Comissão**: Lógica para identificar vendas de terceiros e aplicar a taxa de comissão definida pelo SuperAdmin.
- **Integração de Gateway por Usuário**: Interface para membros configurarem suas credenciais de MercadoPago/PagSeguro via `GatewayAccount`.
- **Chat Integrado no Admin**: Interface de chat rodando dentro do layout AdminLTE.

## Capacidades (Capabilities)
- `admin-cms-management`: Gerenciar conteúdo das páginas `sobre`, `missão`, `hero`, etc.
- `vendor-product-management`: Permitir que membros criem Cursos e Mentorias próprios.
- `payment-split-commission`: Calcular e registrar comissões por venda de instrutores.
- `vendor-gateway-setup`: Interface para membros configurarem seus próprios tokens de pagamento.
- `admin-integrated-chat`: Chat em tempo real integrado ao layout administrativo.

## Impacto
- **Usuários**: Membros ganham autonomia para vender; Administradores ganham controle editorial e receita recorrente por venda.
- **Desenvolvimento**: Reutilização dos controladores de pagamento existentes, agora parametrizados por `GatewayAccount`.
- **Segurança**: Isolamento de credenciais e rotas via middleware de funcionalidades.
