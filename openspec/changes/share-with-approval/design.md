## Context

O sistema atual de compartilhamento no feed social permite três tipos de compartilhamento:
1. **Na própria timeline** (comunidade) - cria um novo post com referência ao original
2. **Para outro membro** - cria um post na timeline do destinatário (sem aprovação)
3. **Redes sociais externas** - abre links do WhatsApp, Facebook, Telegram

O problema está no item 2: quando alguém compartilha para outro membro, o post aparece imediatamente sem consentimento. Isso pode causar spam e conteúdo indesejado.

### Estado atual
- `SocialController::sharePostToUser()` cria o post diretamente com `shared_to_user_id`
- Não existe conceito de "solicitação pendente"
- Posts são criados na tabela `posts` com visibilidade `connections`

## Goals / Non-Goals

**Goals:**
- Permitir que destinatários aprovem/rejeitem compartilhamentos antes de aparecerem
- Criar sistema de notificações para solicitações pendentes
- Manter funcionalidade existente de compartilhar na própria timeline
- Expiração automática de solicitações antigas

**Non-Goals:**
- Modificar compartilhamento em redes sociais externas
- Adicionar moderação de admins em compartilhamentos
- Histórico completo de compartilhamentos rejeitados
- Limite de compartilhamentos por período

## Decisions

### 1. Nova tabela `share_requests`
**Decisão:** Criar tabela separada para solicitações ao invés de usar a tabela `posts` com status.

**Rationale:** Separar responsabilidades - posts são conteúdo final, requests são fluxo de aprovação. Isso evita complicar queries de feed e mantém integridade dos dados.

**Alternativas consideradas:**
- Usar campo `status` na tabela posts → rejeitado por poluir a tabela de posts com itens que nunca serão posts reais

### 2. Fluxo de aprovação inline no feed
**Decisão:** Mostrar solicitações pendentes em uma seção dedicada no feed/perfil, não em página separada.

**Rationale:** Mantém o usuário no contexto do feed, reduz fricção. Notificação leva direto para a solicitação.

**Alternativas consideradas:**
- Página separada de solicitações → mais complexo, menos discoverability

### 3. Expiração em 7 dias
**Decisão:** Solicitações expiram automaticamente após 7 dias sem resposta.

**Rationale:** Evita acúmulo de solicitações antigas. Período de 7 dias é razoável para revisão.

### 4. Notificação via sistema existente
**Decisão:** Usar o sistema de notificações já existente (tabela `notifications` ou similar).

**Rationale:** Reutilizar infraestrutura existente. Não criar novo canal de notificação.

## Risks / Trade-offs

| Risco | Mitigação |
|-------|-----------|
| Usuário pode ignorar solicitações, causando expiração indesejada | Mostrar badge de contagem no menu + notificação inicial |
| Aumento de carga no banco com nova tabela | Índices apropriados + limpeza de expirados via job |
| UX mais complexa para quem compartilha | Feedback claro que é "solicitação" e não "compartilhamento direto" |

## Migration Plan

1. **Criar migration** para tabela `share_requests`
2. **Criar model** ShareRequest com relacionamentos
3. **Modificar controller** `sharePostToUser()` para criar request ao invés de post
4. **Criar rotas** para approve/reject
5. **Atualizar views** do feed para mostrar pendências
6. **Criar job** para expirar requests antigas (opcional, pode ser cron)

**Rollback:** Se necessário, reverter o controller para criar post diretamente (comportamento atual). Requests pendentes podem ser convertidos em posts ou descartados.

## Open Questions

1. Deve haver limite de solicitações pendentes por usuário? (ex: máx 10 pendentes)
2. O remetente deve ser notificado quando sua solicitação é aprovada/rejeitada?
3. Permitir mensagem personalizada junto com a solicitação? (já existe campo `message`)
