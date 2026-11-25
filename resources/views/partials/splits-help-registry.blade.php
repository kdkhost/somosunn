<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.UNNHelpDrawer) return;

    const settingsUrl = @json($settingsPaymentsUrl ?? '#');

    window.UNNHelpDrawer.register({
        'splits_overview': {
            badge: 'Visão Geral',
            title: 'Como funciona a Automação de Comissões',
            summary: 'O sistema calcula e cobra automaticamente sua comissão sobre todas as vendas de cursos, mentorias, eventos e produtos de terceiros, mantendo 100% isentas as vendas criadas ou divulgadas pelo administrador.',
            steps: [
                'Escolha o Modo de Liquidação (recomendado: 100% SumUp + Cobrança Automática no Mercado Pago a cada venda).',
                'Preencha o Access Token da sua conta Mercado Pago onde você receberá as comissões.',
                'Opcionalmente, informe o Customer ID e Card Token do cartão corporativo do administrador para débito 100% automático sem interação humana (se ficar vazio, o sistema gera Pix automático a cada venda).',
                'Ative a chave "Ativar envio automático sem interação humana" e clique em Salvar Automação.'
            ],
            links: [
                { label: 'Painel de Desenvolvedores Mercado Pago', url: 'https://www.mercadopago.com.br/developers/panel/app' },
                { label: 'Painel SumUp (Vendas do Admin)', url: 'https://me.sumup.com/' },
                { label: 'Configurações de Pagamentos do Sistema', url: settingsUrl, external: false }
            ],
            tip: 'Todas as cobranças possuem proteção contra duplicidade (idempotência) e reconciliação automática via webhook do Mercado Pago.'
        },
        'payout_mode': {
            badge: 'Modo de Operação',
            title: 'Modo de Liquidação da Comissão',
            summary: 'Define como o sistema vai liquidar a sua comissão de Superadmin sem que você precise cobrar manualmente.',
            steps: [
                '100% SumUp + Cobrança Automática no MP (a cada venda): o admin continua vendendo 100% na SumUp e, a cada venda de terceiro aprovada, o sistema cobra automaticamente sua comissão na sua conta Mercado Pago (cartão corporativo salvo ou Pix automático).',
                '100% SumUp + Pix Automático (Efí -> MP): o admin recebe a SumUp na conta Efí Pay e o sistema dispara um Pix Out automático para a sua chave Pix do Mercado Pago.',
                'Roteamento Inteligente: vendas do admin passam na SumUp (isentas) e vendas de terceiros passam no Mercado Pago Marketplace com divisão instantânea.',
                'Apenas Controle (Manual): apenas registra os valores devidos na tabela para você confirmar manualmente.'
            ],
            links: [
                { label: 'Documentação Oficial API Mercado Pago', url: 'https://www.mercadopago.com.br/developers/pt/docs' },
                { label: 'Configurações de Gateways do Sistema', url: settingsUrl, external: false }
            ],
            tip: 'Os campos abaixo se adaptam automaticamente ao modo que você escolher, mantendo a tela limpa e organizada.'
        },
        'mp_access_token': {
            badge: 'Credencial Mercado Pago',
            title: 'Access Token da sua Conta Mercado Pago',
            summary: 'É a chave privada de produção da SUA conta Mercado Pago (onde o dinheiro das comissões vai cair). Ela é armazenada com segurança no banco de dados e nunca é exibida novamente em texto aberto.',
            steps: [
                'Acesse o Painel de Desenvolvedores do Mercado Pago usando o link direto abaixo e faça login na SUA conta.',
                'Clique em "Suas integrações" e selecione sua aplicação (ou clique em "Criar aplicação" escolhendo Pagamentos Online > Checkout Transparente).',
                'No menu lateral esquerdo, clique em "Credenciais de produção".',
                'Copie o campo "Access Token" (começa com APP_USR-...) e cole aqui.',
                'Clique em "Salvar Automação".'
            ],
            links: [
                { label: 'Abrir Credenciais de Produção (Mercado Pago)', url: 'https://www.mercadopago.com.br/developers/panel/app' },
                { label: 'Guia oficial: Como obter suas credenciais MP', url: 'https://www.mercadopago.com.br/developers/pt/docs/your-integrations/credentials' }
            ],
            tip: 'Use sempre o Access Token de PRODUÇÃO (APP_USR-...). Se deixar em branco após já ter salvo, o token atual é preservado.'
        },
        'percent': {
            badge: 'Regra de Cálculo',
            title: 'Percentual de Comissão (%)',
            summary: 'Percentual único aplicado automaticamente sobre o valor total de cada venda elegível (cursos, mentorias, eventos e produtos de terceiros).',
            steps: [
                'Informe apenas o número do percentual acordado (exemplo: 10 para 10%, ou 7.5 para 7,5%).',
                'Vendas criadas ou divulgadas pelo administrador (admin/superadmin) continuam automaticamente isentas (0%).',
                'O valor calculado aparece imediatamente na coluna "Valor" da tabela de repasses abaixo.'
            ],
            tip: 'Alterar este percentual passa a valer para todas as novas vendas aprovadas a partir do momento em que você salvar.'
        },
        'min_payout': {
            badge: 'Regra Financeira',
            title: 'Valor Mínimo para Disparo Automático (R$)',
            summary: 'Evita disparar cobranças ou Pix de centavos. Comissões abaixo deste valor ficam registradas normalmente como pendentes até você decidir ou ajustar o mínimo.',
            steps: [
                'Informe o valor mínimo em reais usando ponto para centavos (ex.: 1.00 ou 5.00).',
                'Para cobrar absolutamente qualquer venda de terceiros, mantenha em 1.00.'
            ],
            tip: 'O Mercado Pago aceita cobranças Pix e cartão a partir de R$ 1,00.'
        },
        'admin_email': {
            badge: 'Pagador / Notificações',
            title: 'E-mail Financeiro do Administrador',
            summary: 'E-mail usado como pagador nas cobranças automáticas do Mercado Pago e para enviar notificações/recibos das comissões cobradas.',
            steps: [
                'Informe o e-mail principal do financeiro da empresa que usa o sistema.',
                'Se ficar em branco, o sistema usa automaticamente o e-mail do usuário administrador principal cadastrado.'
            ],
            tip: 'No Mercado Pago, o e-mail do pagador deve ser diferente do e-mail da SUA conta Mercado Pago recebedora.'
        },
        'mp_customer_id': {
            badge: 'Débito Automático 100%',
            title: 'Customer ID do Administrador no Mercado Pago',
            summary: 'Identificador do cliente (empresa administradora) cadastrado na API de Customers do seu Mercado Pago para permitir débito automático no cartão corporativo sem nenhuma interação humana.',
            steps: [
                'Crie um cliente na sua conta Mercado Pago via POST /v1/customers informando o e-mail financeiro do administrador.',
                'Copie o campo "id" retornado pela API (ex.: 123456789-AbCdEf123).',
                'Associe o cartão corporativo do administrador a este Customer ID.',
                'Se não quiser usar cartão corporativo agora, basta deixar este campo em branco: o sistema gerará Pix automático no seu Mercado Pago a cada venda!'
            ],
            links: [
                { label: 'Documentação MP: Salvar Clientes e Cartões', url: 'https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-configuration/card/save-customers-and-cards' },
                { label: 'Referência da API de Customers (Mercado Pago)', url: 'https://www.mercadopago.com.br/developers/pt/reference/customers/_customers/post' }
            ],
            tip: 'Campo opcional: sem Customer ID + Card Token, o sistema funciona em modo Pix automático a cada venda com baixa automática via webhook.'
        },
        'mp_card_token': {
            badge: 'Débito Automático 100%',
            title: 'Card Token do Cartão Corporativo (Mercado Pago)',
            summary: 'Token seguro do cartão corporativo salvo do administrador. Junto com o Customer ID, permite que o sistema debite a sua comissão instantaneamente a cada venda aprovada na SumUp.',
            steps: [
                'Com o Customer ID criado, gere o token do cartão salvo do administrador pela API do Mercado Pago.',
                'Cole o token gerado neste campo e salve a automação.',
                'Caso o cartão recuse ou expire, o sistema aciona automaticamente o fallback via Pix para você nunca perder a cobrança.'
            ],
            links: [
                { label: 'Documentação MP: Cobrar com Cartão Salvo', url: 'https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-configuration/card/save-customers-and-cards' }
            ],
            tip: 'Por segurança, o token salvo fica oculto após gravar. Só preencha novamente se quiser substituir o cartão.'
        },
        'pix_key': {
            badge: 'Chave Pix de Recebimento',
            title: 'Sua Chave Pix do Mercado Pago',
            summary: 'Chave Pix cadastrada na SUA conta Mercado Pago. É usada para receber transferências automáticas via Efí Pay (Pix Out) e exibida como destino oficial nos registros de repasse.',
            steps: [
                'Abra o aplicativo ou site do Mercado Pago na SUA conta.',
                'Acesse Área Pix > Minhas Chaves.',
                'Copie sua chave Pix (CPF, CNPJ, e-mail, telefone no formato +55... ou chave aleatória) e cole aqui.'
            ],
            links: [
                { label: 'Acessar Minha Conta Mercado Pago', url: 'https://www.mercadopago.com.br/home' }
            ],
            tip: 'Recomendamos usar chave aleatória ou e-mail exclusivo para facilitar a conferência no extrato do Mercado Pago.'
        },
        'delay_days': {
            badge: 'Modo Efí Pay',
            title: 'Carência de Liquidação SumUp (D+dias)',
            summary: 'Usado apenas no modo Efí Pay (Pix Out): define quantos dias após a venda o sistema deve aguardar antes de disparar o Pix automático, respeitando o prazo em que a SumUp deposita o dinheiro na conta Efí da empresa.',
            steps: [
                'Verifique no painel da SumUp se os repasses da empresa caem em D+1 (1 dia útil) ou D+2.',
                'Informe 1 para D+1 ou 2 para D+2 (ou 0 para tentar imediatamente).',
                'Mesmo que o saldo ainda não tenha caído, o sistema verifica o saldo antes e reagenda automaticamente a cada hora sem falhar.'
            ],
            links: [
                { label: 'Painel Efí Pay (Gerenciar API Pix)', url: 'https://app.sejaefi.com.br/' },
                { label: 'Configurar Credenciais Efí no Sistema', url: settingsUrl, external: false }
            ],
            tip: 'No modo Opção C (Cobrança Automática no Mercado Pago a cada venda), o disparo acontece imediatamente na aprovação da venda.'
        },
        'auto_enabled': {
            badge: 'Chave Geral',
            title: 'Ativar Envio / Cobrança Automática',
            summary: 'Liga ou desliga o motor automático de comissões. Quando ligado, o sistema opera 100% sem interação humana tanto no instante da venda quanto nas retentativas horárias.',
            steps: [
                'Preencha primeiro as credenciais do modo escolhido.',
                'Marque a opção "Ativar envio automático sem interação humana".',
                'Clique em "Salvar Automação".',
                'Você pode pausar a qualquer momento desmarcando a chave e salvando novamente.'
            ],
            tip: 'Acompanhe os resultados em tempo real na tabela abaixo (status, tentativas e comprovante/ID da transação).'
        }
    });
});
</script>
