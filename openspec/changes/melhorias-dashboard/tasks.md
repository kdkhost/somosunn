## 1. Setup e Estrutura

- [ ] 1.1 Criar/ajustar estrutura de componentes Blade para widgets dinâmicos
- [ ] 1.2 Configurar Redis e Laravel Echo para tempo real (ou fallback polling)
- [ ] 1.3 Adicionar feature flag para liberar dashboards gradualmente

## 2. Implementação dos Widgets e Dashboards

- [ ] 2.1 Refatorar dashboard de membros para exibir widgets por permissão/plano
- [ ] 2.2 Refatorar dashboard de admin para exibir widgets administrativos
- [ ] 2.3 Refatorar dashboard de superadmin para exibir todos os widgets e métricas
- [ ] 2.4 Implementar widgets de visitas, vendas, serviços, produtos, etc.
- [ ] 2.5 Integrar atualização em tempo real nos widgets críticos

## 3. Otimização de Performance

- [ ] 3.1 Otimizar queries com eager loading e chunking
- [ ] 3.2 Implementar cache de métricas com Redis
- [ ] 3.3 Criar jobs para agregação periódica de dados pesados

## 4. Permissões e Segurança

- [ ] 4.1 Centralizar lógica de exibição em traits, policies e middlewares
- [ ] 4.2 Garantir que widgets/métricas respeitem permissões e plano do usuário
- [ ] 4.3 Registrar logs de acesso e tentativas negadas

## 5. Testes e Documentação

- [ ] 5.1 Criar/atualizar testes automatizados para dashboards e widgets
- [ ] 5.2 Testar responsividade e compatibilidade cross-browser
- [ ] 5.3 Atualizar README com instruções de uso e migração
- [ ] 5.4 Documentar setup de tempo real e cache
