## Capability: adminlte-panel

### Requirements

1. O painel AdminLTE deve ser acessível exclusivamente por usuários com role superadmin.
2. Qualquer tentativa de acesso ao painel AdminLTE por outros perfis deve ser bloqueada com mensagem de permissão insuficiente.
3. Todas as funções administrativas do superadmin devem permanecer disponíveis e funcionais no AdminLTE.
4. O AdminLTE não deve ser carregado ou referenciado em rotas/painéis de membros.
5. O controle de acesso deve ser centralizado em middleware e policies, garantindo segurança e isolamento.

### Scenarios

- Superadmin acessa /admin e utiliza todas as funções do AdminLTE normalmente.
- Usuário membro tenta acessar /admin e recebe erro de permissão.
- AdminLTE não impacta performance ou assets do painel de membros.
