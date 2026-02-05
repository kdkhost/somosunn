# Tasks: Sistema Administrativo Total e Marketplace

## CMS Administrativo (Frontend Management)
- [ ] Criar migration para tabela `site_contents` (slug, key, value, type) <!-- id: 0 -->
- [ ] Desenvolver `CMSController` para gerenciar seções da Landing Page <!-- id: 1 -->
- [ ] Criar views Blade no Admin para edição de parágrafos, banners e links <!-- id: 2 -->
- [ ] Implementar helper global `@site()` para recuperar conteúdo dinâmico no frontend <!-- id: 3 -->

## Marketplace de Instrutores e Gateway
- [ ] Criar tela de configuração de Gateway para Membros (usando modelo `GatewayAccount`) <!-- id: 4 -->
- [ ] Adicionar validação de teste de conexão com API do MercadoPago/PagSeguro <!-- id: 5 -->
- [ ] Modificar o `CourseController` para permitir CRUD por quem tiver a flag `feature:vendor` <!-- id: 6 -->

## Divisão de Receita e Comissões
- [ ] Adicionar campo `platform_fee_percent` no modelo `Setting` <!-- id: 7 -->
- [ ] Integrar lógica de Split no checkout (MercadoPago API) <!-- id: 8 -->
- [ ] Criar sistema de logs para cada comissão gerada por venda <!-- id: 9 -->

## Chat Integrado no Admin
- [ ] Mapear rotas de chat para o namespace `Admin` e aplicar layout AdminLTE <!-- id: 10 -->
- [ ] Criar view unificada de chat integrada ao painel administrativo <!-- id: 11 -->

## Documentação e Entrega
- [ ] Atualizar o Manual do Instrutor com os novos passos de configuração de gateway <!-- id: 12 -->
- [ ] Validar fluxo completo: Cadastro -> Compra de Plano VIP -> Criação de Curso -> Venda com Comissão <!-- id: 13 -->
