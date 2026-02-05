# Spec: Sistema de Convite para Conexão

## Descrição
Define o fluxo completo de solicitação de conexão entre dois usuários, desde o envio do convite até a confirmação ou rejeição, com notificações integradas.

## Requisitos
- **Solicitação**: Um usuário deve poder enviar uma solicitação de conexão a partir do perfil de outro usuário.
- **Notificação**: O destinatário deve receber uma notificação visual imediata (ou no próximo poll) informando sobre o novo convite.
- **Ações**: O destinatário pode "Aceitar" ou "Recusar" o convite.
- **Estado**: Enquanto o convite estiver 'pending', o botão no perfil deve refletir esse estado (ex: "Solicitação Enviada").
- **Cancelamento**: O remetente pode cancelar uma solicitação pendente.

## Regras de Negócio
- Não é possível enviar convite para si mesmo.
- Se um usuário já estiver bloqueado, o botão de convite não deve aparecer.
- Se o destinatário recusar, o remetente pode enviar um novo convite após um período de cooldown (opcional) ou o botão volta ao estado inicial.
- Somente quando o status mudar para `accepted`, o sistema de chat será desbloqueado nas próximas specs.

## Dados Necessários
- `user_id`: ID do remetente.
- `friend_id`: ID do destinatário.
- `status`: enum (`pending`, `accepted`, `rejected`).
- `timestamps`: Registros de criação e atualização.

## Integração
- Endpoint API: `POST /connections/request/{user}`.
- Dashboard Widget: Contador de convites pendentes no topo do painel administrativo.
