## 1. Descoberta e modelagem

- [ ] 1.1 Mapear models existentes relacionados a feed, posts, comentarios e curtidas
- [ ] 1.2 Definir entidades novas necessarias e criar migrations (se preciso)
- [ ] 1.3 Definir relacoes Eloquent e indices para performance

## 2. Servicos e regras de negocio

- [ ] 2.1 Criar/ajustar service de feed para criacao e listagem paginada
- [ ] 2.2 Implementar service de interacoes (curtidas, comentarios, compartilhamentos)
- [ ] 2.3 Implementar helper/service de resolucao de foto de perfil com fallback

## 3. Rotas e controllers

- [ ] 3.1 Definir rotas web/api para criar post, listar feed e interagir
- [ ] 3.2 Ajustar controllers para usar services e retornar dados para Blade
- [ ] 3.3 Garantir validacao de entrada e autorizacao nas acoes sociais

## 4. Views Blade e integracao

- [ ] 4.1 Atualizar views do perfil para exibir foto de perfil resolvida
- [ ] 4.2 Atualizar views do feed para usar dados sociais mantendo layout
- [ ] 4.3 Exibir estados de interacao (curtido, contadores, comentarios)

## 5. Testes e validacao

- [ ] 5.1 Criar testes de listagem paginada e criacao de posts
- [ ] 5.2 Criar testes de curtidas/comentarios/compartilhamentos
- [ ] 5.3 Criar testes de resolucao de foto de perfil e fallback
