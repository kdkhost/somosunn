---
description: Publicar alterações no git seguindo o padrão do projeto
---

1. Atualizar o arquivo `README.md` com um resumo das alterações recentes na seção correspondente ou criando uma nova se necessário.
2. Adicionar todos os arquivos modificados:
   ```powershell
   git add .
   ```
3. Criar o commit com uma mensagem humanizada, em português, e que inclua o contexto da solicitação anterior (O QUE foi feito):
   ```powershell
   git commit -m "Tipo: Descrição humanizada do que foi feito (ex: Fix: Correção de upload de imagens)"
   ```
4. Enviar para o repositório remoto:
   ```powershell
   git push origin main
   ```
5. Notificar o usuário que o fluxo foi concluído.
