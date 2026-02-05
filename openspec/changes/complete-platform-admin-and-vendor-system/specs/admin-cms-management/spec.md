# Spec: Gerenciamento Administrativo de Conteúdo (CMS)

## Descrição
Permitir que o administrador edite dinamicamente as seções do frontend sem necessidade de alteração de código ou banco de dados manual.

## Requisitos
- **Seções Editáveis**:
    - Hero (Título, Subtítulo, Imagem de Fundo).
    - Seção "Sobre Nós" (Manifesto, Visão, Valores).
    - Links de Redes Sociais no rodapé.
    - Conteúdo das páginas institucionais.
- **Armazenamento**: Tabela `settings` com suporte a tipos 'text', 'image', 'json'.
- **Upload de Imagens**: Interface integrada com o sistema de arquivos local para troca de banners.

## Interface (Admin)
- Menu "Conteúdo do Site" no painel administrativo.
- Submenus: "Home", "Páginas Institucionais", "Aparência".

## Integração
- Helper Blade `@site('key', 'fallback')` para exibir o conteúdo nas views do frontend.
- Controller: `App\Http\Controllers\Admin\CMSController`.
