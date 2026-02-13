# Resumo das Mudanças - Rodada de UX/Funcionalidades (fev/2026)

## Melhorias implementadas

- **Placeholders e tooltips**: Todos os campos de formulários agora possuem placeholders e tooltips temáticos para melhor orientação do usuário.
- **CEP**: Autofocus automático no próximo campo após preenchimento do CEP.
- **Interesses**: Campo de interesses no perfil agora é um select múltiplo, com persistência correta.
- **CPF/CNPJ**: Seleção explícita do tipo de documento no perfil, com validação dedicada.
- **Gateway de pagamento**: Toggle sandbox/produção e exibição dinâmica dos campos de credenciais.
- **Checkout transparente**: Pagamento MercadoPago integrado diretamente na tela de checkout, sem redirecionamento, com feedback visual e resumo do pedido.
- **Foto de perfil**: Exibição robusta da foto de perfil, com fallback automático e suporte a URLs externas.
- **Menu lateral do chat**: Estado (aberto/minimizado) persiste via localStorage, garantindo experiência contínua ao navegar ou recarregar.

## Observações
- Todas as alterações seguem PSR-12, padrões do projeto e UX em pt-BR.
- Testes manuais realizados para cada fluxo alterado.
- Código pronto para revisão, deploy e novos incrementos.

---

*Gerado automaticamente por GitHub Copilot em 13/02/2026.*
