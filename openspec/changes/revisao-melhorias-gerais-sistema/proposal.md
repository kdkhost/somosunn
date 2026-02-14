## Why

O sistema apresenta pontos de inconsistência e limitações de usabilidade, especialmente em dispositivos móveis (menu não funcional), além de dependências externas para tarefas agendadas (cron). Melhorias são necessárias para garantir funcionamento pleno, experiência consistente, facilidade de manutenção e expansão futura.

## What Changes

- Revisão e correção do menu do frontend para funcionamento perfeito em smartphones e telas pequenas
- Refatoração e melhorias de código visando padronização, performance e escalabilidade
- Implementação de cron interno, gerenciável pelo superadmin no painel, eliminando dependência da hospedagem
- Ajustes e correções gerais para garantir que todas as funções estejam coerentes e operacionais no novo painel

## Capabilities

### New Capabilities
- `internal-cron-management`: Permite configurar e gerenciar tarefas agendadas diretamente pelo painel do superadmin, sem depender do cron da hospedagem
- `mobile-menu-fix`: Garante funcionamento pleno do menu em dispositivos móveis

### Modified Capabilities
- `painel-novo-usuarios`: Ajustes para garantir que todas as funções estejam disponíveis e operacionais para todos os usuários (exceto superadmin) no novo painel, sem alternância para o painel antigo

## Impact

- Código do frontend (menu, responsividade, JS/CSS)
- Backend (rotinas de cron, controllers, jobs)
- Painel do superadmin (UI/UX, configurações)
- Possível refatoração de rotas, views e componentes compartilhados