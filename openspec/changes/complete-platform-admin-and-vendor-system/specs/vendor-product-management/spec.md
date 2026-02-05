# Spec: Gestão de Produtos por Membros (Instrutores)

## Descrição
Habilitar membros com planos específicos para criar e gerenciar seus próprios cursos e mentorias dentro da plataforma.

## Requisitos
- **Filtro de Plano**: Somente planos com a flag `feature:vendor` podem acessar o menu "Meus Produtor".
- **Propriedade**: Cada curso/mentoria deve estar vinculado ao `user_id` do membro.
- **Limitação**: O Admin pode definir um limite de cursos por plano (ex: Plano VIP = 5 cursos).
- **Aprovação**: Opção para o Admin aprovar ou reprovar cursos de terceiros antes de serem publicados globalmente.

## Fluxo de Trabalho
1. Membro acessa "Painel do Instrutor".
2. Preenche dados do curso (título, preço, gateway).
3. Solicita publicação.
4. Admin aprova.

## Integração
- Extensão do `CourseController` para suportar filtragem por criador em vez de apenas admin.
