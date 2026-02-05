# Design: Arquitetura de Chat e Conexões (Hospedagem Compartilhada)

## Visão Geral
O sistema utilizará o banco de dados como fonte da verdade para o estado das conexões e mensagens. Para simular o "tempo real" em um ambiente cPanel sem WebSockets, utilizaremos **Long Polling** ou **Interval Polling Otimizado** no frontend (JavaScript/Alpine.js) que consulta endpoints leves no Laravel.

## Arquitetura Técnica

### 1. Modelo de Dados (Database)
- **Tabela `connections`**:
    - `user_id` (FK)
    - `friend_id` (FK)
    - `status` (enum: `pending`, `accepted`, `rejected`, `blocked`)
    - `hide_profile` (boolean)
- **Tabela `messages`** (Já existente, mas será vinculada ao status da conexão):
    - Verificação de `status == 'accepted'` antes de permitir a inserção ou recuperação.

### 2. Fluxo de Reconhecimento e Privacidade
- **Middleware/Gate de Busca**: Ao listar usuários, o sistema aplicará um filtro `WHERE NOT EXISTS` em registros de bloqueio ou `hide_profile`.
- **Notificações**: Utilizaremos a coluna `unread_notifications` na sessão ou uma tabela de notificações flash para alertar sobre novos convites.

### 3. Sincronização em Tempo Real (cPanel Friendly)
- **Backend**: Endpoint `/chat/sync` que aceita um `last_message_id`. Retorna apenas novas mensagens em JSON.
- **Frontend**: 
    - Uso de `setInterval` (ex: a cada 3-5 segundos) para verificar novas mensagens apenas se a janela do chat estiver ativa.
    - Otimização: Se o usuário estiver inativo por X minutos, o intervalo aumenta para poupar processamento do servidor.

### 4. Componentes de UI
- **Botão de Conexão**: Alterna entre "Solicitar", "Pendente" e "Chat" (se aceito).
- **Interface de Chat**: Integrada ao layout administrativo (AdminLTE), mantendo a persistência da janela entre navegações se possível (via sessionStorage ou similar).

## Decisões e Trocas (Trade-offs)
- **Polling vs WebSockets**: Polling consome mais requisições HTTP, mas é 100% compatível com cPanel sem necessidade de processos daemon (como Swoole ou Node.js).
- **Criptografia**: Mensagens serão armazenadas em texto plano (ou com hash leve) no DB, priorizando performance de busca em hospedagem compartilhada.

## Riscos
- Sobrecarga de requisições se houver muitos usuários simultâneos. **Solução**: Cache de sessão para checagem de status de conexão e debounce agressivo no frontend.
