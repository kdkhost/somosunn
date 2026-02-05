# Spec: Chat Integrado ao Painel Administrativo

## Descrição
Migrar a experiência de chat (clone do Facebook/Comunidade) para dentro do layout do Painel Administrativo AdminLTE.

## Requisitos
- **Layout Unificado**: O chat deve rodar dentro do `content-wrapper` do admin, respeitando o cabeçalho e a barra lateral do painel.
- **Persistent Sidebar**: Lista de contatos recentes/conectados disponível na lateral do painel admin.
- **Responsividade**: Deve funcionar corretamente no mobile dentro do menu sanduíche do admin.

## Integração
- View: `resources/views/admin/chat/index.blade.php`.
- Reutilização dos componentes Vue/Alpine do sistema de mensagens atual.
