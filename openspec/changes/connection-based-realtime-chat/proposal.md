# Proposta: Chat em Tempo Real Baseado em Conexão Aceita

## Objetivo
Implementar um sistema de chat seguro e instantâneo onde a comunicação entre membros só é permitida após uma solicitação de conexão ser explicitamente aceita. O sistema deve suportar notificações de convite, bloqueio de usuários, ocultação de perfil e funcionar de forma fluida em ambiente de hospedagem compartilhada (PHP 8.4 / Laravel 10).

## Motivação
Aumentar a segurança e a privacidade dos membros da comunidade, garantindo que o chat não seja usado para spam e que o contato ocorra apenas entre pessoas que concordaram em se conectar. Além disso, garantir que a experiência seja "real-time" mesmo em servidores que não suportam protocolos complexos de WebSocket.

## O que vai mudar
- **Sistema de Convites**: Nova lógica de solicitação de conexão com notificações em tempo real.
- **Controle de Privacidade**: Funcionalidade para recusar convites, bloquear usuários e ocultar o perfil da busca/visualização para usuários específicos.
- **Ativação Condicional do Chat**: O ícone e a janela de chat só serão liberados para o par de usuários quando o status da conexão for 'accepted'.
- **Motor de Chat Instantâneo**: Implementação de uma camada de sincronização via AJAX/Long Polling otimizada para manter as mensagens atualizadas sem refresh de página, respeitando os limites de recursos de hospedagem cPanel.

## Capacidades (Capabilities)
- `connection-invitation-system`: Solicitação, aceite e recusa de conexões com notificações.
- `privacy-and-blocking-controls`: Bloqueio de usuários e ocultação de presença/perfil.
- `conditional-chat-activation`: Liberação dinâmica da interface de chat baseada no status da conexão.
- `optimized-realtime-messages`: Sincronização de mensagens sem refresh usando técnicas compatíveis com cPanel.

## Impacto
- **Usuários**: Maior controle sobre quem pode entrar em contato e uma experiência de conversa moderna.
- **Infraestrutura**: Uso eficiente de recursos do servidor PHP 8.4, evitando a complexidade de servidores WebSocket dedicados em ambiente compartilhado.
- **Segurança**: Redução de interações indesejadas e assédio através do sistema de bloqueio e filtros de busca.
