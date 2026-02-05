# Spec: Sincronização de Mensagens Otimizada (cPanel)

## Descrição
Implementação de um mecanismo de atualização de mensagens "em tempo real" que seja leve e compatível com as limitações de processos e recursos de uma hospedagem compartilhada.

## Requisitos
- **Polling Inteligente**: O frontend (Alpine.js ou JS puro) fará requisições a cada 3 segundos iniciais.
- **Intervalo Adaptativo**: Se a aba estiver em background ou o usuário estiver inativo por mais de 5 minutos, o intervalo de poll sobe para 15-30 segundos.
- **Payload Leve**: O backend deve retornar apenas o JSON com o campo `last_id` e o array de novas mensagens desserializadas.
- **Sem Refresh**: As mensagens devem ser anexadas ao DOM de forma instantânea sem recarregar a página.

## Regras de Negócio
- Utilizar cache do Laravel para armazenar temporariamente o status "online" e as últimas mensagens para reduzir consultas pesadas ao DB em cada poll.
- Limitar o número de mensagens retornadas por poll para evitar payloads gigantes.

## Integração
- Endpoint: `GET /chat/sync?last_id={id}&conversation={id}`.
- Script Frontend: Centralizado em `resources/js/chat-engine.js`.
