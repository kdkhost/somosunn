<?php

namespace App\Support;

class LegalPageContent
{
    /**
     * @return array<int, array{slug:string,title:string,data:array<string,mixed>}>
     */
    public static function definitions(): array
    {
        return [
            [
                'slug' => 'termos-de-uso',
                'title' => 'Termos de Uso',
                'data' => [
                    'seo_title' => 'Termos de Uso - SOMOS UNN',
                    'seo_description' => 'Regras para uso da plataforma SOMOS UNN, incluindo comunidade, cursos, mentorias, eventos, vagas, pagamentos e indicações.',
                    'hero_title' => 'Termos de Uso',
                    'hero_subtitle' => 'Condições para acesso, compras, cursos, mentorias, eventos, comunidade, vagas e recursos comerciais da plataforma.',
                    'body_content' => self::termsBody(),
                ],
            ],
            [
                'slug' => 'politica-de-privacidade',
                'title' => 'Política de Privacidade',
                'data' => [
                    'seo_title' => 'Política de Privacidade - SOMOS UNN',
                    'seo_description' => 'Como a SOMOS UNN trata dados cadastrais, profissionais, financeiros, acadêmicos e de navegação na plataforma.',
                    'hero_title' => 'Política de Privacidade',
                    'hero_subtitle' => 'Como tratamos dados cadastrais, profissionais, financeiros, acadêmicos e de navegação na plataforma SOMOS UNN.',
                    'body_content' => self::privacyBody(),
                ],
            ],
            [
                'slug' => 'consentimento-lgpd',
                'title' => 'Consentimento LGPD',
                'data' => [
                    'seo_title' => 'Consentimento LGPD - SOMOS UNN',
                    'seo_description' => 'Entenda o aceite do modal, as bases legais aplicáveis e como exercer seus direitos como titular de dados.',
                    'hero_title' => 'Consentimento LGPD',
                    'hero_subtitle' => 'Entenda o aceite do modal, as bases legais aplicáveis e como exercer seus direitos como titular de dados.',
                    'body_content' => self::lgpdConsentBody(),
                ],
            ],
        ];
    }

    private static function termsBody(): string
    {
        return <<<'HTML'
<p><strong>Última atualização:</strong> 24/03/2026.</p>
<p>Estes Termos de Uso disciplinam o acesso e a utilização da plataforma SOMOS UNN, ambiente digital que reúne funcionalidades de networking, comunidade, cursos, mentorias, eventos, vagas, programa de indicação, área comercial, cupons, parceiros e conteúdos educacionais. Ao navegar, cadastrar-se, contratar um plano ou utilizar qualquer recurso autenticado da plataforma, você declara que leu, compreendeu e concorda com estes Termos, com a Política de Privacidade e com o documento de Consentimento LGPD vinculados ao modal de aceite.</p>

<h2>1. Objeto e escopo da plataforma</h2>
<p>A SOMOS UNN oferece, entre outros, os seguintes serviços: cadastro e gestão de perfil profissional; feed social e interações entre membros; conexões, mensagens e networking; cursos com aulas, progresso e certificados; mentorias e sessões; eventos com inscrições, ingressos e check-in; oportunidades de trabalho e candidatura; benefícios, cupons e parceiros; programa de indicação e recursos analíticos; e contratação de planos, produtos e serviços próprios ou de terceiros anunciados no ambiente.</p>
<p>Alguns recursos podem variar conforme o plano contratado, o perfil do usuário, permissões concedidas pelo administrador, regras específicas de cada oferta e disponibilidade técnica da plataforma.</p>

<h2>2. Cadastro, elegibilidade e segurança da conta</h2>
<p>Para utilizar áreas autenticadas, o usuário deve fornecer dados verdadeiros, atualizados e completos, manter a confidencialidade de suas credenciais e responder por toda atividade realizada em sua conta. O compartilhamento indevido de login, o uso de identidade falsa ou a criação de conta para terceiros sem autorização são proibidos.</p>
<p>Ao se cadastrar, o usuário declara possuir capacidade legal para contratar e utilizar os serviços. Quando agir em nome de empresa, equipe, projeto ou terceiro, declara possuir autorização suficiente para isso.</p>

<h2>3. Regras de conduta e uso aceitável</h2>
<ul>
    <li>utilizar a plataforma de forma lícita, ética e compatível com a finalidade dos serviços;</li>
    <li>respeitar direitos de terceiros, incluindo imagem, privacidade, propriedade intelectual e honra;</li>
    <li>não publicar conteúdo ilícito, ofensivo, discriminatório, enganoso, fraudulento, difamatório, pornográfico ou que incentive violência;</li>
    <li>não praticar spam, automação abusiva, scraping, engenharia reversa, envio massivo não autorizado ou tentativa de contornar controles técnicos da plataforma;</li>
    <li>não usar a SOMOS UNN para coletar dados pessoais de outros usuários sem base legal adequada;</li>
    <li>não burlar regras de pagamento, comissionamento, check-in, certificados, ranking, cupons, afiliados ou campanhas promocionais.</li>
</ul>
<p>A SOMOS UNN poderá moderar conteúdo, limitar funcionalidades, suspender publicações, cancelar benefícios ou bloquear contas que violem estes Termos, regras da comunidade, exigências legais ou a segurança do ambiente.</p>

<h2>4. Conteúdo do usuário e licenças de uso</h2>
<p>O usuário permanece titular dos direitos sobre textos, imagens, vídeos, comentários, currículo, materiais de perfil e demais conteúdos que enviar, salvo nos limites legais aplicáveis. Ao publicar ou enviar conteúdo na plataforma, o usuário concede à SOMOS UNN licença não exclusiva, gratuita e pelo prazo necessário à operação para hospedar, exibir, organizar, reproduzir tecnicamente e disponibilizar esse conteúdo dentro dos serviços contratados, inclusive para fins de moderação, backup, segurança e funcionamento da plataforma.</p>
<p>O usuário declara possuir autorização para publicar os conteúdos e responde integralmente por eventuais violações a direitos de terceiros.</p>

<h2>5. Cursos, mentorias, eventos, certificados e oportunidades</h2>
<p>A plataforma pode disponibilizar cursos, aulas, anexos, avaliações, mentorias, eventos presenciais ou online, certificados, galerias de mídia e vagas. Cada item pode possuir regras complementares de preço, carga horária, agenda, cancelamento, número de vagas, critérios de emissão de certificado, política de participação, local, disponibilidade e requisitos específicos informados na própria oferta.</p>
<p>A SOMOS UNN não garante resultado econômico, empregabilidade, fechamento de negócios, aprovação em processos seletivos ou sucesso comercial a partir do uso da plataforma. Em fluxos de vagas e recrutamento, a decisão final sobre seleção, contato e contratação pertence à empresa, recrutador ou responsável pela vaga.</p>

<h2>6. Pagamentos, planos, assinaturas e benefícios</h2>
<p>Parte dos serviços pode depender de pagamento único, assinatura recorrente, compra de ingresso, contratação de mentoria ou aquisição de curso/produto. Os pagamentos podem ser processados por provedores terceirizados, inclusive Mercado Pago, conforme a modalidade disponível no checkout.</p>
<p>O acesso pago somente é liberado após confirmação do pagamento, observadas as regras antifraude, conciliação financeira e eventuais análises do provedor. Reembolsos, cancelamentos, períodos de acesso, renovação, inadimplência, benefícios do plano e elegibilidade a cupons ou vantagens poderão seguir regras específicas informadas na página do produto, no checkout, na oferta promocional ou em comunicações oficiais da plataforma.</p>

<h2>7. Parceiros, cupons e programa de indicação</h2>
<p>A SOMOS UNN pode oferecer benefícios de parceiros, cupons, campanhas promocionais, marketplace e programa de indicação com rastreamento de visitas, cadastros, checkouts e compras. Esses recursos podem possuir critérios próprios de validade, elegibilidade, limite de uso, geografia, período promocional, estoque, regras antifraude e possibilidade de suspensão em caso de abuso.</p>
<p>A plataforma poderá cancelar pontuações, comissões, benefícios, cupons ou recompensas obtidas por fraude, autoindicação irregular, simulação de tráfego, múltiplas contas, chargeback, manipulação de eventos de conversão ou descumprimento das regras da campanha.</p>

<h2>8. Privacidade e proteção de dados</h2>
<p>O tratamento de dados pessoais realizado na SOMOS UNN observa a Lei nº 13.709/2018 (LGPD), a Política de Privacidade e o documento de Consentimento LGPD disponíveis no site. O uso da plataforma pode envolver tratamento de dados cadastrais, profissionais, financeiros, comportamentais, educacionais e de navegação, sempre de acordo com a finalidade do serviço e com a base legal aplicável.</p>

<h2>9. Propriedade intelectual</h2>
<p>O software, a identidade visual, marcas, nome comercial, layout, bases organizadas, trilhas, textos institucionais, materiais próprios, templates, elementos gráficos e demais ativos da SOMOS UNN são protegidos pela legislação aplicável. Salvo autorização expressa, é proibido copiar, redistribuir, modificar, vender, sublicenciar ou explorar comercialmente esses elementos fora das hipóteses permitidas em lei.</p>

<h2>10. Disponibilidade, limitações e responsabilidade</h2>
<p>A SOMOS UNN emprega esforços razoáveis para manter a plataforma disponível e segura, mas não garante operação ininterrupta, isenção absoluta de falhas, indisponibilidades temporárias, perda de conexão, incompatibilidades de dispositivo ou erros de terceiros. Manutenções, atualizações, integrações externas, incidentes de segurança, falhas de provedores ou medidas legais podem afetar temporariamente o funcionamento de recursos específicos.</p>
<p>Na máxima extensão permitida pela legislação aplicável, a plataforma não responde por atos de terceiros, negociações privadas entre usuários, condutas de parceiros, conteúdos publicados por membros ou decisões tomadas por recrutadores, instrutores, mentores, organizadores e anunciantes independentes.</p>

<h2>11. Suspensão, cancelamento e encerramento de conta</h2>
<p>A SOMOS UNN poderá restringir, suspender ou encerrar contas e conteúdos quando houver indícios de fraude, inadimplência, violação destes Termos, risco à segurança, uso abusivo da infraestrutura, determinação legal ou regulatória, ou necessidade de preservar direitos de terceiros e da comunidade. O usuário também pode solicitar o encerramento da conta pelos canais oficiais, ressalvadas obrigações legais ou regulatórias de retenção de dados, histórico financeiro, prevenção a fraudes e defesa em processos.</p>

<h2>12. Atualizações destes Termos</h2>
<p>Estes Termos podem ser alterados para refletir mudanças legais, regulatórias, operacionais ou de produto. Quando a atualização impactar o uso da área autenticada, a SOMOS UNN poderá solicitar nova ciência e novo aceite dos documentos vinculados ao modal LGPD antes da continuidade do acesso.</p>

<h2>13. Contato</h2>
<p>Dúvidas, solicitações e comunicações relacionadas a estes Termos podem ser encaminhadas pelos canais oficiais da plataforma, inclusive pela <a href="/contato">página de contato</a>.</p>
HTML;
    }

    private static function privacyBody(): string
    {
        return <<<'HTML'
<p><strong>Última atualização:</strong> 24/03/2026.</p>
<p>Esta Política de Privacidade descreve como a SOMOS UNN trata dados pessoais no contexto de sua plataforma de networking, comunidade, cursos, mentorias, eventos, vagas, pagamentos, indicações e recursos administrativos. O tratamento observa a Lei nº 13.709/2018 (LGPD) e demais normas aplicáveis.</p>
<p>No contexto desta plataforma, a SOMOS UNN atua como controladora em relação aos dados necessários ao cadastro, autenticação, faturamento, comunidade, ensino, eventos, indicações, segurança e suporte. Em determinadas integrações, terceiros podem atuar como operadores ou controladores independentes, como meios de pagamento, empresas recrutadoras, instrutores, mentores, organizadores de eventos e parceiros comerciais, conforme o contexto da operação.</p>

<h2>1. Quais dados pessoais podemos tratar</h2>
<ul>
    <li><strong>Dados cadastrais e de autenticação:</strong> nome, e-mail, telefone, senha criptografada, foto, cargo, empresa, cidade, links profissionais e demais informações de perfil.</li>
    <li><strong>Dados de uso da comunidade:</strong> posts, comentários, curtidas, compartilhamentos, conexões, mensagens, preferências, interações com o ranking e histórico de participação.</li>
    <li><strong>Dados educacionais e de participação:</strong> inscrições, progresso em aulas, certificados, avaliações, presença em eventos, check-in e consumo de mentorias.</li>
    <li><strong>Dados comerciais e financeiros:</strong> plano contratado, pedidos, pagamentos, status de cobrança, dados de transação, repasses, cupons, comissões e histórico de compras.</li>
    <li><strong>Dados profissionais e de oportunidades:</strong> currículo, portfólio, respostas enviadas em candidaturas, histórico de aplicação e informações fornecidas a recrutadores ou empresas anunciante.</li>
    <li><strong>Dados de navegação e segurança:</strong> IP, data e hora de acesso, dispositivo, navegador, logs, identificadores de sessão, trilhas de uso, cookies e eventos de rastreamento, inclusive em jornadas de indicação e afiliados.</li>
    <li><strong>Dados sensíveis eventualmente fornecidos pelo usuário:</strong> a plataforma não exige, por padrão, dados pessoais sensíveis, mas eles podem ser tratados se o próprio usuário os inserir em currículo, perfil, formulários, certificados, suporte ou documentos enviados.</li>
</ul>

<h2>2. Finalidades e bases legais</h2>
<p>Conforme a LGPD e as orientações da ANPD, o tratamento de dados pessoais pode ocorrer com diferentes bases legais, e o consentimento é apenas uma delas. Na SOMOS UNN, os tratamentos podem ocorrer, por exemplo, para:</p>
<ul>
    <li><strong>execução de contrato e procedimentos preliminares:</strong> criação de conta, matrícula, assinatura, compra de curso, ingresso, mentoria, emissão de certificado, gestão de plano e atendimento de solicitações do usuário;</li>
    <li><strong>cumprimento de obrigação legal ou regulatória:</strong> guarda de registros, documentos fiscais, prevenção à fraude, atendimento a ordens de autoridades e cumprimento de deveres legais;</li>
    <li><strong>legítimo interesse:</strong> segurança da informação, auditoria, melhoria do produto, prevenção a abusos, métricas de uso, comunicações operacionais, moderação e proteção da comunidade;</li>
    <li><strong>exercício regular de direitos:</strong> defesa em processos judiciais, administrativos ou arbitrais e gestão de evidências;</li>
    <li><strong>proteção do crédito e do pagamento:</strong> autenticação de transações, conciliação, cobrança e gestão de inadimplência;</li>
    <li><strong>consentimento, quando aplicável:</strong> comunicações promocionais, publicações opcionais, tratamentos facultativos, cookies não essenciais e fluxos específicos em que a lei ou a operação exijam autorização do titular.</li>
</ul>

<h2>3. Com quem podemos compartilhar dados</h2>
<p>Os dados pessoais podem ser compartilhados, na medida necessária à operação, com:</p>
<ul>
    <li>provedores de hospedagem, armazenamento, envio de e-mail, autenticação, analytics, atendimento, mídia e infraestrutura tecnológica;</li>
    <li>meios de pagamento, antifraude, conciliação financeira, gateways e instituições relacionadas à cobrança;</li>
    <li>instrutores, mentores, organizadores de eventos, vendedores, parceiros ou responsáveis pela oferta contratada, quando isso for necessário para entrega do serviço;</li>
    <li>empresas, recrutadores e responsáveis por vagas quando o usuário se candidatar, hipótese em que esses terceiros poderão atuar como controladores independentes quanto à seleção;</li>
    <li>outros usuários da plataforma, na extensão configurada pelo próprio titular em seu perfil, posts, comentários, mensagens, conexões e participações públicas;</li>
    <li>autoridades públicas, órgãos reguladores e Poder Judiciário, quando houver obrigação legal, regulatória ou ordem válida.</li>
</ul>

<h2>4. Exposição pública e visibilidade dentro da plataforma</h2>
<p>Dados de perfil, conteúdo publicado no feed, comentários, conexões, materiais enviados, certificados, vagas, ofertas ou demais interações poderão ficar visíveis a outros usuários ou ao público, conforme a natureza da funcionalidade e as configurações disponíveis. Antes de publicar informações profissionais, comerciais ou currículos, o usuário deve avaliar se deseja torná-las visíveis a terceiros.</p>

<h2>5. Cookies, identificadores e tecnologias semelhantes</h2>
<p>A SOMOS UNN pode utilizar cookies, pixels, identificadores locais e outras tecnologias para autenticação de sessão, segurança, memorização de preferências, análise de desempenho, mensuração de campanhas, rastreamento de links de indicação e melhoria da navegação. Parte desses recursos é necessária ao funcionamento da conta; outros poderão depender de configuração específica, aviso complementar ou consentimento, quando exigido.</p>

<h2>6. Retenção e descarte</h2>
<p>Os dados pessoais são mantidos pelo período necessário para cumprir as finalidades desta Política, viabilizar a execução dos serviços, resguardar direitos da plataforma, atender obrigações legais ou regulatórias, prevenir fraudes e manter trilhas mínimas de auditoria. Mesmo após o encerramento da conta, determinados registros podem ser conservados pelos prazos legalmente exigidos ou necessários para defesa da SOMOS UNN, de parceiros e dos próprios titulares.</p>

<h2>7. Segurança da informação</h2>
<p>A plataforma adota medidas técnicas e administrativas razoáveis para proteger dados pessoais contra acesso não autorizado e situações acidentais ou ilícitas de destruição, perda, alteração, comunicação ou tratamento inadequado, em linha com o art. 46 da LGPD. Ainda assim, nenhum ambiente digital é absolutamente invulnerável. Caso ocorra incidente de segurança relevante, a comunicação será realizada nos termos da legislação aplicável.</p>

<h2>8. Transferências internacionais</h2>
<p>Alguns fornecedores de tecnologia, armazenamento, comunicação e processamento podem manter infraestrutura fora do Brasil ou utilizar processamento internacional. Nesses casos, a SOMOS UNN busca adotar medidas contratuais e operacionais compatíveis com a LGPD e com o nível de risco da operação.</p>

<h2>9. Direitos do titular</h2>
<p>Nos termos da LGPD, o titular pode solicitar, entre outros direitos, confirmação da existência de tratamento, acesso, correção, anonimização, bloqueio, eliminação, portabilidade, informações sobre compartilhamento, revogação do consentimento quando essa for a base legal aplicável e revisão de decisões automatizadas, quando cabível.</p>
<p>As solicitações podem ser feitas pelos canais oficiais da plataforma, inclusive pela <a href="/contato">página de contato</a>. Para proteger a conta e evitar fraude, a SOMOS UNN poderá solicitar informações adicionais para confirmar a identidade do solicitante. Caso o atendimento não seja possível integralmente, a resposta indicará o fundamento legal aplicável. Se o titular não conseguir exercer seus direitos perante a plataforma ou considerar a resposta insatisfatória, poderá peticionar à ANPD, conforme as orientações públicas da autoridade.</p>

<h2>10. Atualizações desta Política</h2>
<p>Esta Política poderá ser atualizada para refletir mudanças legais, regulatórias, contratuais, operacionais ou tecnológicas. Quando a alteração impactar significativamente o uso da área autenticada ou o modo como os dados são tratados, a SOMOS UNN poderá solicitar nova ciência e novo aceite no modal de documentos legais.</p>
HTML;
    }

    private static function lgpdConsentBody(): string
    {
        return <<<'HTML'
<p><strong>Última atualização:</strong> 24/03/2026.</p>
<p>Este documento explica o alcance do aceite solicitado no modal LGPD exibido ao membro ou usuário autenticado da SOMOS UNN. O objetivo é registrar a ciência inequívoca dos documentos legais da plataforma e esclarecer em quais hipóteses o tratamento de dados pessoais depende de consentimento e em quais hipóteses outras bases legais da LGPD são utilizadas.</p>

<h2>1. O que significa o aceite no modal</h2>
<p>Ao marcar o campo de aceite e confirmar no modal, o usuário declara que leu e compreendeu os Termos de Uso, a Política de Privacidade e este documento de Consentimento LGPD. O aceite é registrado eletronicamente e vinculado à conta do usuário com data, hora, endereço IP, agente de navegação e versão vigente dos documentos, para fins de segurança, auditoria e comprovação de conformidade.</p>
<p>Sem a ciência e o aceite dos documentos vinculados ao ambiente autenticado, o acesso às áreas restritas da plataforma não é liberado.</p>

<h2>2. Consentimento não é a única base legal</h2>
<p>Conforme a LGPD e as orientações da ANPD, o tratamento de dados pessoais pode ocorrer com bases legais diferentes do consentimento, especialmente nas hipóteses dos arts. 7º e 11 da Lei nº 13.709/2018. Isso significa que nem todo tratamento realizado pela SOMOS UNN depende de autorização expressa do titular.</p>
<p>Operações necessárias para criação da conta, autenticação, contratação de plano, compra de curso, emissão de certificado, gestão de pagamento, prevenção à fraude, segurança do ambiente, atendimento, guarda de registros, execução de contrato e cumprimento de obrigação legal podem ocorrer com outras bases legais aplicáveis, mesmo que o usuário venha a revogar consentimentos facultativos posteriormente.</p>

<h2>3. Em quais situações o consentimento pode ser utilizado</h2>
<p>Quando a operação exigir ou recomendar consentimento, ele poderá abranger, por exemplo:</p>
<ul>
    <li>envio de comunicações promocionais, campanhas, convites, newsletters e ofertas não estritamente necessárias à prestação do serviço contratado;</li>
    <li>tratamento de dados facultativos informados pelo próprio usuário em campos opcionais do perfil, currículo, portfólio, galeria, depoimentos, páginas públicas ou materiais enviados para divulgação;</li>
    <li>uso de cookies, pixels e identificadores não essenciais, quando implementados com aviso específico e controle apropriado;</li>
    <li>participação em ações promocionais, programas especiais, pesquisas, benefícios de parceiros ou funcionalidades opcionais que apresentem pedido próprio de autorização.</li>
</ul>
<p>O aceite do modal não substitui consentimentos específicos que possam ser solicitados em fluxos próprios da plataforma.</p>

<h2>4. Direitos do titular</h2>
<p>Nos termos do art. 18 da LGPD e das orientações públicas da ANPD, o titular pode solicitar confirmação da existência de tratamento, acesso aos dados, correção de dados incompletos ou desatualizados, anonimização, bloqueio ou eliminação quando cabível, portabilidade, informações sobre compartilhamento, informação sobre a possibilidade de não fornecer consentimento, revogação do consentimento e revisão de decisões automatizadas, quando aplicável.</p>
<p>O exercício desses direitos deve ser solicitado primeiro diretamente à SOMOS UNN pelos canais oficiais da plataforma. Caso o pedido não seja atendido ou a resposta seja considerada insatisfatória, o titular poderá utilizar os canais públicos de petição da ANPD, conforme instruções da autoridade.</p>

<h2>5. Revogação do consentimento</h2>
<p>O consentimento pode ser revogado a qualquer momento, mediante solicitação pelos canais oficiais da plataforma, sem necessidade de justificativa. A revogação não invalida os tratamentos realizados anteriormente de forma regular e também não impede a manutenção de tratamentos baseados em obrigação legal, execução contratual, legítimo interesse, exercício regular de direitos, segurança ou outras bases legais aplicáveis.</p>
<p>Dependendo da funcionalidade afetada, a revogação pode limitar o recebimento de campanhas, a participação em determinadas ações promocionais ou o uso de recursos opcionais que dependam de autorização do titular.</p>

<h2>6. Nova versão e renovação do aceite</h2>
<p>Quando houver alteração relevante no conteúdo dos documentos legais, a SOMOS UNN poderá gerar nova versão e solicitar novo aceite no próximo acesso autenticado. Isso garante que o registro de consentimento permaneça vinculado ao texto efetivamente vigente no momento do uso da plataforma.</p>

<h2>7. Canais de contato</h2>
<p>Solicitações relacionadas a privacidade, proteção de dados e exercício de direitos podem ser encaminhadas pelos canais oficiais da plataforma, inclusive pela <a href="/contato">página de contato</a>.</p>
HTML;
    }
}
