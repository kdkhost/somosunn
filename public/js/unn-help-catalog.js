(function () {
    if (!window.UNNHelpDrawer) return;

    window.UNNHelpDrawer.register({
        /* ===== MERCADO PAGO ===== */
        'mercadopago_env': {
            badge: 'Mercado Pago',
            title: 'Ambiente do Mercado Pago (Sandbox / Produção)',
            summary: 'Alterna dinamicamente entre o ambiente de testes (Sandbox) e o ambiente real de cobranças (Produção).',
            steps: [
                'Use Sandbox para testar pagamentos com cartões de teste sem movimentar dinheiro real.',
                'Quando tudo estiver validado, alterne para Produção e preencha as chaves APP_USR-...',
                'Os campos abaixo mudam automaticamente conforme o ambiente selecionado.'
            ],
            links: [
                { label: 'Painel de Desenvolvedores Mercado Pago', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ],
            tip: 'Mantenha o ambiente em Produção para aceitar vendas reais de membros com split automático.'
        },
        'mercadopago_sandbox_public_key': {
            badge: 'Mercado Pago • Sandbox',
            title: 'Public Key de Testes (Sandbox)',
            summary: 'Chave pública usada no frontend (Checkout Transparente / Cartão) durante testes em Sandbox.',
            steps: [
                'Acesse o Painel de Desenvolvedores do Mercado Pago.',
                'Abra sua aplicação e vá em Credenciais de teste.',
                'Copie a Public Key (começa com TEST-...) e cole neste campo.'
            ],
            links: [
                { label: 'Obter Credenciais de Teste MP', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ]
        },
        'mercadopago_sandbox_access_token': {
            badge: 'Mercado Pago • Sandbox',
            title: 'Access Token de Testes (Sandbox)',
            summary: 'Chave privada usada pelo servidor para criar pagamentos e testar webhooks em ambiente Sandbox.',
            steps: [
                'No Painel de Desenvolvedores do Mercado Pago, acesse Credenciais de teste.',
                'Copie o Access Token (começa com TEST-...) e cole aqui.',
                'Salve e clique em Testar Conexão para validar.'
            ],
            links: [
                { label: 'Obter Credenciais de Teste MP', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ]
        },
        'mercadopago_prod_public_key': {
            badge: 'Mercado Pago • Produção',
            title: 'Public Key de Produção',
            summary: 'Chave pública oficial usada no checkout real do site para tokenizar cartões com segurança.',
            steps: [
                'Acesse o Painel de Desenvolvedores do Mercado Pago.',
                'Abra sua aplicação e clique em Credenciais de produção.',
                'Copie a Public Key (começa com APP_USR-...) e cole aqui.'
            ],
            links: [
                { label: 'Obter Credenciais de Produção MP', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ]
        },
        'mercadopago_prod_access_token': {
            badge: 'Mercado Pago • Produção',
            title: 'Access Token de Produção',
            summary: 'Chave privada oficial da conta Mercado Pago da plataforma para processar cobranças reais e receber a taxa de split (30%).',
            steps: [
                'Acesse o Painel de Desenvolvedores do Mercado Pago e clique em Credenciais de produção.',
                'Copie o Access Token (APP_USR-...) e cole neste campo.',
                'Nunca compartilhe este token publicamente.'
            ],
            links: [
                { label: 'Obter Credenciais de Produção MP', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ],
            tip: 'Para split automático de 30% funcionar nas vendas de membros, configure também o Client ID e Client Secret (OAuth).'
        },
        'mercadopago_client_id': {
            badge: 'Mercado Pago • OAuth / Split',
            title: 'Client ID (ID da Aplicação)',
            summary: 'Identificador da sua aplicação Marketplace no Mercado Pago, necessário para os membros conectarem suas contas via OAuth e receberem 70% direto.',
            steps: [
                'Abra sua aplicação no Painel de Desenvolvedores do Mercado Pago.',
                'No menu lateral, clique em Detalhes da aplicação.',
                'Copie o número de "Número do aplicativo / Client ID" e cole aqui.'
            ],
            links: [
                { label: 'Detalhes da Aplicação (Mercado Pago)', url: 'https://www.mercadopago.com.br/developers/panel/app' },
                { label: 'Documentação Split Marketplace MP', url: 'https://www.mercadopago.com.br/developers/pt/docs/split-payments/landing' }
            ]
        },
        'mercadopago_client_secret': {
            badge: 'Mercado Pago • OAuth / Split',
            title: 'Client Secret (Segredo da Aplicação)',
            summary: 'Chave secreta usada para concluir a autorização OAuth dos vendedores e renovar os tokens automaticamente.',
            steps: [
                'Em Detalhes da aplicação no Mercado Pago Developers, clique em Credenciais / Client Secret.',
                'Copie o segredo e cole neste campo.'
            ],
            links: [
                { label: 'Detalhes da Aplicação (Mercado Pago)', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ]
        },
        'mercadopago_webhook_secret': {
            badge: 'Mercado Pago • Segurança',
            title: 'Chave Secreta do Webhook (Assinatura)',
            summary: 'Valida criptograficamente que toda notificação recebida no webhook veio realmente do Mercado Pago.',
            steps: [
                'No Mercado Pago Developers, vá em Webhooks > Configurar notificações.',
                'Cadastre a URL de Webhook exibida na tela e marque o evento "Pagamentos".',
                'Copie a "Assinatura secreta" gerada e cole aqui.'
            ],
            links: [
                { label: 'Configurar Webhooks no Mercado Pago', url: 'https://www.mercadopago.com.br/developers/panel/app' }
            ]
        },

        /* ===== SUMUP ===== */
        'sumup_env': {
            badge: 'SumUp • Exclusivo Admin',
            title: 'Ambiente SumUp (Sandbox / Produção)',
            summary: 'Alterna entre o ambiente de testes (Sandbox) e o ambiente real (Produção) da SumUp. Lembrando: apenas vendas do administrador utilizam SumUp.',
            steps: [
                'Selecione Sandbox para validar a integração com chaves de teste.',
                'Selecione Produção para processar vendas reais do administrador.'
            ],
            links: [
                { label: 'Acessar Painel SumUp', url: 'https://me.sumup.com/' }
            ]
        },
        'sumup_api_key': {
            badge: 'SumUp • Credencial',
            title: 'SumUp API Key (Chave Secreta)',
            summary: 'Chave secreta server-to-server usada para criar checkouts e consultar transações na conta SumUp do administrador.',
            steps: [
                'Acesse me.sumup.com e entre na conta do administrador.',
                'Vá em Configurações > Para Desenvolvedores > Toolkit > API Keys.',
                'Crie uma nova Secret API Key (começa com sup_sk_...) e cole aqui.'
            ],
            links: [
                { label: 'Abrir API Keys na SumUp', url: 'https://me.sumup.com/' },
                { label: 'Documentação Oficial SumUp API', url: 'https://developer.sumup.com/' }
            ],
            tip: 'Use sempre a Secret API Key (sup_sk_...) e nunca a Public Key.'
        },
        'sumup_merchant_code': {
            badge: 'SumUp • Identificação',
            title: 'Merchant Code (Código do Lojista)',
            summary: 'Código curto identificador da conta SumUp que receberá os pagamentos do administrador (ex.: MXXXXXXX).',
            steps: [
                'No painel SumUp, abra seu perfil de comerciante ou clique em Testar Conexão após colar a API Key.',
                'Copie o Merchant Code (código começando com M) e informe neste campo.'
            ],
            links: [
                { label: 'Acessar Painel SumUp', url: 'https://me.sumup.com/' }
            ]
        },
        'sumup_client_id': {
            badge: 'SumUp • OAuth',
            title: 'SumUp Client ID (Opcional)',
            summary: 'Utilizado apenas se você desejar autenticação OAuth avançada com a SumUp. Para pagamentos padrão, apenas API Key + Merchant Code já bastam.',
            steps: [
                'Acesse developer.sumup.com > OAuth Apps.',
                'Copie o Client ID caso esteja usando OAuth.'
            ],
            links: [
                { label: 'Portal Developers SumUp', url: 'https://developer.sumup.com/' }
            ]
        },
        'sumup_client_secret': {
            badge: 'SumUp • OAuth',
            title: 'SumUp Client Secret (Opcional)',
            summary: 'Segredo OAuth complementar ao Client ID da SumUp.',
            steps: [
                'Em developer.sumup.com > OAuth Apps, copie o Client Secret da sua aplicação.'
            ],
            links: [
                { label: 'Portal Developers SumUp', url: 'https://developer.sumup.com/' }
            ]
        },

        /* ===== EFÍ PAY ===== */
        'efi_env': {
            badge: 'Efí Pay • Pix',
            title: 'Ambiente Efí Pay (Homologação / Produção)',
            summary: 'Alterna o endpoint da API Pix da Efí Pay entre Homologação (pix-h.api.efipay.com.br) e Produção (pix.api.efipay.com.br).',
            steps: [
                'Em Homologação, utilize Client ID, Client Secret e certificado .p12 gerados no ambiente de testes da Efí.',
                'Em Produção, utilize as credenciais e certificado de produção da conta oficial.'
            ],
            links: [
                { label: 'Acessar Painel Efí Pay', url: 'https://app.sejaefi.com.br/' }
            ]
        },
        'efi_client_id': {
            badge: 'Efí Pay • Credencial',
            title: 'Client ID da Efí Pay',
            summary: 'Identificador OAuth2 da aplicação criada na conta Efí Pay para gerar cobranças Pix e enviar Pix Out automático.',
            steps: [
                'Acesse app.sejaefi.com.br e vá em API > Aplicações.',
                'Crie ou abra sua aplicação e habilite os escopos Pix (cob.write, cob.read, pix.read, pix.send, gn.balance.read).',
                'Copie o Client ID correspondente ao ambiente selecionado.'
            ],
            links: [
                { label: 'Aplicações API Efí Pay', url: 'https://app.sejaefi.com.br/' }
            ]
        },
        'efi_client_secret': {
            badge: 'Efí Pay • Credencial',
            title: 'Client Secret da Efí Pay',
            summary: 'Chave secreta OAuth2 da aplicação Efí Pay (usada em conjunto com o certificado mTLS).',
            steps: [
                'Na mesma tela de API > Aplicações da Efí Pay, copie o Client Secret do ambiente escolhido e cole aqui.'
            ],
            links: [
                { label: 'Aplicações API Efí Pay', url: 'https://app.sejaefi.com.br/' }
            ]
        },
        'efi_pix_key': {
            badge: 'Efí Pay • Pix',
            title: 'Chave Pix da Conta Efí Pay',
            summary: 'Chave Pix cadastrada na conta Efí Pay usada como origem/recebedora das operações Pix via API.',
            steps: [
                'No painel Efí Pay, acesse Pix > Minhas Chaves.',
                'Copie uma chave ativa (preferencialmente chave aleatória) e cole neste campo.'
            ],
            links: [
                { label: 'Gerenciar Chaves Pix na Efí', url: 'https://app.sejaefi.com.br/' }
            ]
        },

        /* ===== LOGIN SOCIAL ===== */
        'social_google_client_id': {
            badge: 'Login Social • Google',
            title: 'Google Client ID (OAuth 2.0)',
            summary: 'Permite que visitantes e membros façam login ou cadastro com 1 clique usando a conta Google.',
            steps: [
                'Acesse o Google Cloud Console > APIs e Serviços > Credenciais.',
                'Crie um ID do cliente OAuth 2.0 do tipo "Aplicativo da Web".',
                'Adicione a URL de callback exibida na tela em "URIs de redirecionamento autorizados".',
                'Copie o Client ID e cole aqui.'
            ],
            links: [
                { label: 'Abrir Google Cloud Console', url: 'https://console.cloud.google.com/apis/credentials' }
            ]
        },
        'social_google_client_secret': {
            badge: 'Login Social • Google',
            title: 'Google Client Secret',
            summary: 'Chave secreta gerada junto com o Google Client ID para autenticar o login com Google.',
            steps: [
                'No Google Cloud Console, abra a credencial OAuth criada e copie a "Chave secreta do cliente".'
            ],
            links: [
                { label: 'Abrir Google Cloud Console', url: 'https://console.cloud.google.com/apis/credentials' }
            ]
        },
        'social_facebook_client_id': {
            badge: 'Login Social • Facebook',
            title: 'Facebook App ID',
            summary: 'Identificador do aplicativo no Meta for Developers para login com Facebook.',
            steps: [
                'Acesse developers.facebook.com/apps e selecione seu app.',
                'Vá em Configurações do App > Básico e copie o ID do Aplicativo.'
            ],
            links: [
                { label: 'Abrir Meta for Developers', url: 'https://developers.facebook.com/apps/' }
            ]
        },
        'social_linkedin_client_id': {
            badge: 'Login Social • LinkedIn',
            title: 'LinkedIn Client ID',
            summary: 'Identificador da aplicação no LinkedIn Developers para login profissional com 1 clique.',
            steps: [
                'Acesse linkedin.com/developers/apps, abra seu app e clique na aba Auth.',
                'Copie o Client ID e cadastre a URL de redirecionamento autorizada.'
            ],
            links: [
                { label: 'Abrir LinkedIn Developers', url: 'https://www.linkedin.com/developers/apps' }
            ]
        },

        /* ===== SMTP / E-MAIL ===== */
        'smtp_host': {
            badge: 'E-mail • SMTP',
            title: 'Servidor SMTP (Host)',
            summary: 'Endereço do servidor responsável por enviar os e-mails transacionais do sistema (boas-vindas, pedidos, faturas, recuperação de senha).',
            steps: [
                'Consulte no painel da sua hospedagem, cPanel, Brevo, Amazon SES ou Zoho Mail o endereço do servidor SMTP (ex.: mail.seudominio.com.br ou smtp-relay.brevo.com).',
                'Informe apenas o domínio do servidor, sem http://.'
            ],
            tip: 'Após salvar, use o botão de enviar e-mail de teste para validar a entrega.'
        },
        'smtp_port': {
            badge: 'E-mail • SMTP',
            title: 'Porta SMTP',
            summary: 'Porta de conexão segura com o servidor de e-mails.',
            steps: [
                'Use 465 quando a criptografia for SSL.',
                'Use 587 quando a criptografia for TLS (STARTTLS).'
            ]
        },
        'smtp_username': {
            badge: 'E-mail • SMTP',
            title: 'Usuário / E-mail SMTP',
            summary: 'Conta de e-mail ou login de autenticação fornecida pelo seu provedor SMTP.',
            steps: [
                'Geralmente é o endereço completo de e-mail (ex.: naoresponda@somosunn.com.br).'
            ]
        },
        'smtp_password': {
            badge: 'E-mail • SMTP',
            title: 'Senha SMTP',
            summary: 'Senha da conta de e-mail ou chave de API SMTP do seu provedor.',
            steps: [
                'Cole a senha da caixa postal ou a "Senha de aplicativo" gerada no provedor.'
            ]
        },

        /* ===== ARMAZENAMENTO S3 / R2 ===== */
        'storage_driver': {
            badge: 'Armazenamento',
            title: 'Driver de Armazenamento de Arquivos',
            summary: 'Define se imagens, vídeos e uploads ficam no disco local do servidor ou em nuvem compatível com S3 (Cloudflare R2, AWS S3, Wasabi, DigitalOcean Spaces).',
            steps: [
                'Local (public): armazena diretamente na hospedagem atual.',
                'S3 / Compatível: envia uploads para bucket em nuvem, economizando espaço e acelerando carregamento via CDN.'
            ],
            links: [
                { label: 'Cloudflare R2 (Storage sem taxa de egress)', url: 'https://dash.cloudflare.com/' },
                { label: 'Console AWS S3', url: 'https://s3.console.aws.amazon.com/' }
            ]
        },
        's3_key': {
            badge: 'Armazenamento • S3 / R2',
            title: 'Access Key ID (S3 / R2)',
            summary: 'Chave de acesso pública da credencial de API do seu bucket S3 ou Cloudflare R2.',
            steps: [
                'No Cloudflare R2 ou AWS IAM, crie um token de API com permissão de leitura e gravação no bucket.',
                'Copie o Access Key ID e cole neste campo.'
            ]
        },
        's3_secret': {
            badge: 'Armazenamento • S3 / R2',
            title: 'Secret Access Key (S3 / R2)',
            summary: 'Chave secreta gerada junto com a Access Key ID para assinar os uploads no bucket.',
            steps: [
                'Copie o Secret Access Key no momento da criação do token no seu provedor S3/R2.'
            ]
        },
        's3_bucket': {
            badge: 'Armazenamento • S3 / R2',
            title: 'Nome do Bucket',
            summary: 'Nome exato do bucket criado no seu provedor de armazenamento em nuvem.',
            steps: [
                'Informe exatamente como foi criado (letras minúsculas e hífens, sem espaços).'
            ]
        },

        /* ===== SEO, ANALYTICS E SEGURANÇA ===== */
        'google_analytics_id': {
            badge: 'SEO & Métricas',
            title: 'ID do Google Analytics (GA4)',
            summary: 'Conecta o site ao Google Analytics 4 para acompanhar acessos, páginas mais vistas e conversões.',
            steps: [
                'Acesse analytics.google.com > Administrador > Fluxos de dados.',
                'Copie o "ID da métrica" (começa com G-XXXXXXXXXX) e cole aqui.'
            ],
            links: [
                { label: 'Abrir Google Analytics', url: 'https://analytics.google.com/' }
            ]
        },
        'meta_pixel_id': {
            badge: 'Marketing • Meta Ads',
            title: 'ID do Pixel da Meta (Facebook / Instagram)',
            summary: 'Rastreia eventos de navegação e conversão para otimizar campanhas de anúncios no Instagram e Facebook.',
            steps: [
                'Acesse o Gerenciador de Eventos da Meta (business.facebook.com).',
                'Copie o número de identificação do seu Pixel e cole aqui.'
            ],
            links: [
                { label: 'Abrir Meta Business Suite', url: 'https://business.facebook.com/events_manager2' }
            ]
        },
        'recaptcha_v3_site_key': {
            badge: 'Segurança • Anti-Bot',
            title: 'reCAPTCHA v3 Site Key',
            summary: 'Protege formulários de login, cadastro e contato contra robôs sem incomodar usuários reais.',
            steps: [
                'Acesse o painel do Google reCAPTCHA e crie um site do tipo v3.',
                'Adicione os domínios do seu sistema e copie a Chave do site.'
            ],
            links: [
                { label: 'Abrir Google reCAPTCHA Admin', url: 'https://www.google.com/recaptcha/admin' }
            ]
        },
        'recaptcha_v3_secret_key': {
            badge: 'Segurança • Anti-Bot',
            title: 'reCAPTCHA v3 Secret Key',
            summary: 'Chave secreta usada no servidor para validar a pontuação do reCAPTCHA v3.',
            steps: [
                'No mesmo painel do Google reCAPTCHA, copie a "Chave secreta" e cole aqui.'
            ],
            links: [
                { label: 'Abrir Google reCAPTCHA Admin', url: 'https://www.google.com/recaptcha/admin' }
            ]
        },

        /* ===== REDES SOCIAIS / PERFIL ===== */
        'website': {
            badge: 'Perfil & Links',
            title: 'Website Oficial',
            summary: 'Você pode digitar com ou sem https:// (ex.: meusite.com.br). O sistema normaliza automaticamente para um link clicável seguro.',
            steps: [
                'Informe o endereço do seu site principal ou portfólio.'
            ]
        },
        'instagram': {
            badge: 'Perfil & Links',
            title: 'Instagram',
            summary: 'Aceita qualquer formato: @seuusuario, seuusuario ou link completo https://instagram.com/seuusuario.',
            steps: [
                'Digite seu @ ou cole o link do seu perfil — o sistema converte automaticamente para o link correto.'
            ]
        },
        'facebook': {
            badge: 'Perfil & Links',
            title: 'Facebook',
            summary: 'Aceita /seuusuario, seuusuario ou URL completa do Facebook.',
            steps: [
                'Informe o nome da sua página ou cole o link completo do Facebook.'
            ]
        },
        'linkedin': {
            badge: 'Perfil & Links',
            title: 'LinkedIn',
            summary: 'Aceita /in/seuusuario, seuusuario ou link completo do LinkedIn.',
            steps: [
                'Informe seu perfil profissional do LinkedIn em qualquer formato.'
            ]
        },
        'youtube': {
            badge: 'Perfil & Links',
            title: 'YouTube',
            summary: 'Aceita @seucanal, /c/seucanal ou link completo do YouTube.',
            steps: [
                'Informe o identificador ou link do seu canal.'
            ]
        }
    });

    if (typeof window.UNNHelpDrawer.autoAttach === 'function') {
        window.UNNHelpDrawer.autoAttach();
    }
})();
