# Spec: Ativação Condicional do Chat

## Descrição
Lógica que controla a exibição da interface de chat, garantindo que ela só esteja disponível para usuários que possuem uma conexão ativa e aceita.

## Requisitos
- **Interface Dinâmica**: O ícone do chat e o botão "Enviar Mensagem" no perfil do usuário só devem ser renderizados se `connection_status == 'accepted'`.
- **Validação de Backend**: Todos os endpoints de envio e recebimento de mensagens (`/chat/*`) devem validar se há uma conexão 'accepted' entre os participantes.
- **Encerramento Automático**: Caso uma conexão seja removida ou alterada para 'blocked', o chat deve ser desativado imediatamente na próxima atualização do polling.

## Regras de Negócio
- Admins/Superadmins têm bypass nesta regra (podem iniciar chat com qualquer um sem convite formal, conforme política da plataforma).
- Status `pending` ou `rejected` não permitem abertura de chat.

## Integração
- Middleware de Rota: Um novo middleware (ou extensão do `CheckFeature`) para validar o status da conexão em rotas de chat.
- Blade Directives: Criação de uma diretiva `@canChat($user)` para simplificar o uso nas views.
