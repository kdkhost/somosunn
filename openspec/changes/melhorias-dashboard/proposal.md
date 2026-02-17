## Why

O sistema atual possui dashboards que não refletem em tempo real as métricas essenciais para cada tipo de usuário, além de não adaptar completamente os widgets e informações conforme o plano, permissões ou papel do membro. Isso dificulta a tomada de decisão, reduz a usabilidade e pode sobrecarregar o banco de dados com consultas ineficientes. Com o crescimento da base de usuários e a diversificação dos planos, é fundamental modernizar e otimizar as dashboards para garantir performance, segurança e experiência personalizada.

## What Changes

- Refatoração das dashboards de membros, admin e superadmin para exibir apenas informações relevantes conforme permissões, plano e papel do usuário.
- Implementação de widgets dinâmicos e gráficos em tempo real (ex: visitas, vendas, serviços, produtos, etc.), otimizando queries e uso de cache.
- Adaptação visual e responsiva dos painéis, com uso de Tailwind para membros e AdminLTE apenas para superadmin.
- Otimização de todas as consultas, updates e deletes para evitar sobrecarga no banco de dados.
- Centralização da lógica de permissões e exibição de widgets em traits, policies e middlewares customizados.
- Inclusão de logs e métricas de acesso para auditoria e monitoramento.

## Capabilities

### New Capabilities
- `dashboard-dinamica`: Dashboard que adapta widgets, métricas e gráficos conforme permissões, plano e papel do usuário, com atualização em tempo real.
- `dashboard-performance`: Otimização de queries, uso de cache e agregação para evitar sobrecarga no banco de dados.

### Modified Capabilities
- `painel-membros`: Ampliação dos requisitos para dashboards dinâmicas e widgets por permissão/plano.
- `permissao-dinamica`: Reforço da lógica de exibição condicional de widgets e métricas.

## Impact

- Refatoração de controllers, views e middlewares relacionados a dashboards.
- Ajustes em traits de permissões e acesso a features.
- Novos jobs/events para atualização em tempo real (ex: Laravel Echo, Redis/Pusher).
- Alterações em policies, rotas e arquivos de configuração.
- Possível criação/ajuste de tabelas para métricas agregadas (ex: visitas, vendas).
- Atualização de testes automatizados e documentação (README).
