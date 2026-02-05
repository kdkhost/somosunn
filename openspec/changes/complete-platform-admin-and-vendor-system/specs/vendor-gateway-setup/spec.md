# Spec: Configuração de Gateway do Instrutor

## Descrição
Interface para que o instrutor configure suas próprias credenciais de pagamento para receber diretamente pelas vendas de seus cursos.

## Requisitos
- **Formulário de Configuração**: Campos para `Public Key` e `Access Token` (encritpados).
- **Validação**: Botão "Testar Conexão" que faz uma chamada de API simples ao gateway para validar as chaves.
- **Multitenancy**: Se um curso pertence ao Usuário A, o checkout desse curso deve usar o perfil do GatewayAccount do Usuário A.

## Interface
- Menu "Configurações de Pagamento" dentro da área do Instrutor.

## Dados
- Tabela `gateway_accounts` vinculada ao `user_id`.
