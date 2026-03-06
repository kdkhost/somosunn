## Why

O programa de afiliados já rastreia cliques e conversões, mas ainda não entrega material pronto para divulgação nem uma API REST própria para o membro montar páginas, painéis ou microsites externos usando seu link de indicação. Isso limita a capacidade de aquisição dos afiliados e gera dependência total da interface interna.

## What Changes

- Adicionar um kit de divulgação no painel do afiliado com textos prontos, CTA, blocos de landing page, ativos da marca e ofertas recomendadas já parametrizadas com o link do membro.
- Expor endpoints REST autenticados para o afiliado consumir seus dados, materiais de divulgação, ofertas e métricas em ferramentas externas.
- Disponibilizar uma visão estruturada de landing page para uso em sites externos, com hero, benefícios, prova social e CTAs.
- Incluir documentação prática no painel para uso da API com token pessoal.

## Capabilities

### New Capabilities
- `affiliate-share-kit`: Materiais de divulgação prontos para compartilhamento e uso em landing pages afiliadas.
- `affiliate-promo-api`: API REST autenticada para o afiliado consumir link, métricas, ofertas e estrutura de páginas promocionais.

### Modified Capabilities
- Nenhuma.

## Impact

- Código afetado: `app/Http/Controllers/Panel/ReferralController.php`, `resources/views/panel/referral/index.blade.php`, `routes/api.php`, novos controllers/resources/services de afiliados.
- APIs: novos endpoints autenticados para afiliados e possível expansão do payload do painel.
- Sistemas: painel do afiliado, autenticação Sanctum, conteúdo institucional/CMS e catálogo público de planos, cursos, eventos e mentorias.
