## 1. Setup e Estrutura

- [x] 1.1 Criar/ajustar estrutura de componentes Blade para widgets dinamicos
- [ ] 1.2 Configurar Redis e Laravel Echo para tempo real (ou fallback polling)
- [x] 1.3 Adicionar feature flag para liberar dashboards gradualmente (via canAccessFeature)

## 2. Implementacao dos Widgets e Dashboards

- [x] 2.1 Refatorar dashboard de membros para exibir widgets por permissao/plano
- [x] 2.2 Refatorar dashboard de admin para exibir widgets administrativos
- [x] 2.3 Refatorar dashboard de superadmin para exibir todos os widgets e metricas
- [x] 2.4 Implementar widgets de visitas, vendas, servicos, produtos, etc.
- [x] 2.5 Integrar atualizacao automatica (polling fetch a cada 10s)

## 3. Otimizacao de Performance

- [x] 3.1 Otimizar queries com eager loading e chunking
- [ ] 3.2 Implementar cache de metricas com Redis
- [ ] 3.3 Criar jobs para agregacao periodica de dados pesados

## 4. Permissoes e Seguranca

- [x] 4.1 Centralizar logica de exibicao em traits, policies e middlewares
- [x] 4.2 Garantir que widgets/metricas respeitem permissoes e plano do usuario
- [ ] 4.3 Registrar logs de acesso e tentativas negadas

## 5. Testes e Documentacao

- [ ] 5.1 Criar/atualizar testes automatizados para dashboards e widgets
- [ ] 5.2 Testar responsividade e compatibilidade cross-browser
- [ ] 5.3 Atualizar README com instrucoes de uso e migracao
- [ ] 5.4 Documentar setup de tempo real e cache