## Capability: painel-membros

### Requirements

1. O painel de membros deve ser acessível apenas para usuários com role diferente de superadmin.
2. O layout do painel de membros deve seguir o padrão visual do site público, utilizando Tailwind CSS e componentes responsivos.
3. Todas as funções administrativas disponíveis no AdminLTE devem estar presentes no painel de membros, respeitando permissões, planos e pacotes.
4. O painel deve ser totalmente responsivo, sem quebra de botões ou componentes em diferentes tamanhos de tela.
5. O acesso a cada funcionalidade deve ser controlado por policies e traits customizados (HasRoles, HasFeatureAccess).
6. O painel de membros não deve carregar dependências do AdminLTE.
7. O painel deve garantir experiência fluida e sem erros em todas as telas, inclusive em dispositivos móveis.

### Scenarios

- Usuário membro acessa /painel e visualiza interface moderna, responsiva e alinhada ao site.
- Usuário tenta acessar rota restrita (ex: /admin) e recebe erro de permissão.
- Superadmin acessa /admin e mantém experiência AdminLTE.
- Botões e componentes se adaptam corretamente em telas pequenas, sem sobreposição ou quebra de layout.
- Permissões de funcionalidades variam conforme plano/pacote do usuário.
