## Capability: permissao-dinamica

### Requirements

1. O sistema deve aplicar permissões de acesso a rotas, controllers e views com base no role, plano e pacote do usuário.
2. Traits HasRoles e HasFeatureAccess devem ser utilizados para centralizar a lógica de permissões.
3. Policies devem ser revisadas e aplicadas para cada funcionalidade sensível.
4. O middleware check.role e check.plan deve ser obrigatório em todas as rotas administrativas.
5. Permissões devem ser facilmente ajustáveis pelo superadmin, sem necessidade de alterar código.
6. Logs de acesso e tentativas negadas devem ser registrados para auditoria.

### Scenarios

- Usuário com plano básico acessa apenas funções permitidas pelo seu pacote.
- Superadmin ajusta permissões de um plano e a mudança reflete imediatamente para todos os membros.
- Tentativa de acesso não autorizado é bloqueada e registrada em log.
- Policies impedem acesso a recursos sensíveis mesmo em caso de falha de middleware.
