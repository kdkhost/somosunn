# Spec: Controles de Privacidade e Bloqueio

## Descrição
Gerenciamento de restrições de visibilidade e bloqueio de interações indesejadas para garantir a segurança dos membros.

## Requisitos
- **Bloqueio**: Um usuário deve poder bloquear outro usuário diretamente pelo seu perfil ou pela lista de conexões.
- **Efeito de Bloqueio**: 
    - O usuário bloqueado não pode enviar novos convites.
    - O usuário bloqueado não pode enviar mensagens no chat (a interface deve sumir).
    - As mensagens anteriores no chat tornam-se inacessíveis para o bloqueado.
- **Ocultação de Perfil**: Opção de "Esconder meu perfil de [Usuário X]" mesmo sem bloqueio total (apenas visualização).
- **Desbloqueio**: O usuário que bloqueou deve ter uma lista de "Usuários Bloqueados" para gerenciar o desbloqueio.

## Regras de Negócio
- O bloqueio é unilateral: somente quem bloqueou pode desbloquear.
- Usuários bloqueados são excluídos automaticamente de resultados de busca global para quem os bloqueou.
- Notificações de convites pendentes de um usuário bloqueado são removidas automaticamente.

## Dados Necessários
- `status`: 'blocked' na tabela de conexões.
- Atributo `hide_profile` (booleano) para controle fino de visualização.

## Integração
- Endpoint API: `POST /connections/block/{user}`.
- View: Modal de confirmação de bloqueio com aviso sobre as consequências.
