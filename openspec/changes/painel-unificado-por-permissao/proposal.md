## Why

O sistema atual utiliza o painel AdminLTE 3.2 para todos os usuários, independentemente do seu papel (role). Isso gera limitações de experiência, segurança e escalabilidade, pois membros comuns e administradores compartilham a mesma interface e lógica de permissões. Separar o painel administrativo permitirá uma experiência mais adequada para cada perfil, maior controle de permissões e facilitará a expansão futura do sistema.

## What Changes

- Separar o painel administrativo: apenas superadmin continuará usando o AdminLTE 3.2.
- Criar um novo painel para membros/usuários comuns, com layout alinhado ao site público (Tailwind, responsivo, sem dependências do AdminLTE).
- Replicar todas as funções existentes do admin para o novo painel, respeitando permissões, planos e pacotes.
- Corrigir e reforçar as permissões de acesso em todas as rotas, controllers e views.
- Ajustar botões e componentes para responsividade e coerência visual.
- Garantir que não haja quebras de layout ou funcionalidades em nenhuma tela.

## Capabilities

### New Capabilities
- painel-membros: painel administrativo unificado para membros/usuários não-superadmin, com layout próprio, responsivo e permissões dinâmicas.

### Modified Capabilities
- adminlte-panel: restrito apenas a superadmin, removendo acesso de outros perfis.
- permissao-dinamica: reforço e ajuste das permissões de acesso conforme role, plano e pacote.

## Impact

- Controllers e middlewares de autenticação e autorização.
- Views e layouts (novos arquivos para painel de membros, ajustes no painel adminlte).
- Rotas protegidas por role e permissões.
- CSS/JS: inclusão de novos assets e ajustes de responsividade.
- Testes de acesso e usabilidade para todos os perfis.
