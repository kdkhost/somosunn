## Why

Hoje a area de perfil e feed nao entrega uma experiencia social fluida, semelhante ao que os usuarios esperam de um feed tipo Facebook. Precisamos melhorar interacoes, exibicao de conteudo e integracao de foto de perfil para reduzir friccao e aumentar engajamento.

## What Changes

- Tornar a area de perfil e feed funcional como um feed social (posts, comentarios, curtidas e compartilhamento), sem alterar a aparencia atual.
- Puxar automaticamente a foto de perfil ja configurada do usuario e exibi-la de forma consistente na area de perfil e no feed.
- Ajustar endpoints e logica de exibicao para suportar o fluxo social (criacao, listagem e interacao com posts).

## Capabilities

### New Capabilities
- `social-feed-core`: feed social com criacao, listagem e interacoes basicas (curtidas, comentarios, compartilhamentos) preservando o layout atual.
- `profile-photo-sync`: sincronizacao e exibicao da foto de perfil ja configurada do usuario em todas as entradas do feed e no perfil.

### Modified Capabilities
- (vazio)

## Impact

- Models/Controllers relacionados a perfil, usuarios e feed.
- Views Blade da area de perfil e feed para integrar dados sem mudar o layout.
- Rotas web/api para criacao e interacao no feed.
- Possiveis ajustes em servicos de upload/armazenamento de foto de perfil.
