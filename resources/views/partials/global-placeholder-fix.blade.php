<style>
    :root {
        --unn-placeholder-claro: #64748b;
        --unn-placeholder-escuro: #94a3b8;
    }

    input::placeholder,
    textarea::placeholder {
        color: var(--unn-placeholder-claro) !important;
        opacity: 1 !important;
    }

    html.dark input::placeholder,
    html.dark textarea::placeholder,
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder {
        color: var(--unn-placeholder-escuro) !important;
        opacity: 1 !important;
    }

    input::-webkit-input-placeholder,
    textarea::-webkit-input-placeholder {
        color: var(--unn-placeholder-claro) !important;
        opacity: 1 !important;
    }

    html.dark input::-webkit-input-placeholder,
    html.dark textarea::-webkit-input-placeholder,
    body.dark-mode input::-webkit-input-placeholder,
    body.dark-mode textarea::-webkit-input-placeholder {
        color: var(--unn-placeholder-escuro) !important;
        opacity: 1 !important;
    }

    input:-ms-input-placeholder,
    textarea:-ms-input-placeholder {
        color: var(--unn-placeholder-claro) !important;
        opacity: 1 !important;
    }

    html.dark input:-ms-input-placeholder,
    html.dark textarea:-ms-input-placeholder,
    body.dark-mode input:-ms-input-placeholder,
    body.dark-mode textarea:-ms-input-placeholder {
        color: var(--unn-placeholder-escuro) !important;
        opacity: 1 !important;
    }
</style>

<script>
    (function () {
        const TIPOS_IGNORADOS = new Set([
            'hidden',
            'file',
            'checkbox',
            'radio',
            'range',
            'color',
            'button',
            'submit',
            'reset',
            'image'
        ]);

        const PALAVRAS_PTBR = [
            'digite', 'informe', 'selecione', 'escolha', 'pesquise',
            'nome', 'sobrenome', 'apelido', 'usuário', 'usuario',
            'e-mail', 'email', 'senha', 'confirmação', 'confirmacao',
            'telefone', 'celular', 'endereço', 'endereco', 'bairro',
            'cidade', 'estado', 'país', 'pais', 'cep', 'descrição', 'descricao',
            'mensagem', 'código', 'codigo', 'cupom', 'valor', 'preço', 'preco',
            'data', 'hora', 'cliente', 'aplicativo', 'chave', 'segredo'
        ];

        const MAPA_FRASES = [
            ['type here', 'Digite aqui'],
            ['search here', 'Pesquise aqui'],
            ['search', 'Pesquisar'],
            ['select an option', 'Selecione uma opção'],
            ['select option', 'Selecione uma opção'],
            ['choose option', 'Escolha uma opção'],
            ['full name', 'Nome completo'],
            ['first name', 'Nome'],
            ['last name', 'Sobrenome'],
            ['user name', 'Nome de usuário'],
            ['username', 'Nome de usuário'],
            ['email address', 'E-mail'],
            ['confirm password', 'Confirmar senha'],
            ['password confirmation', 'Confirmação de senha'],
            ['phone number', 'Telefone'],
            ['postal code', 'CEP'],
            ['zip code', 'CEP'],
            ['client id', 'ID do cliente'],
            ['client secret', 'Segredo do cliente'],
            ['app id', 'ID do aplicativo'],
            ['app secret', 'Segredo do aplicativo'],
            ['api key', 'Chave da API'],
            ['api secret', 'Segredo da API'],
            ['access token', 'Token de acesso'],
            ['refresh token', 'Token de atualização'],
            ['certificate of achievement', 'Certificado de conclusão'],
            ['this certificate is proudly present to', 'Este certificado é orgulhosamente concedido a'],
            ['instagram url', 'URL do Instagram'],
            ['facebook url', 'URL do Facebook'],
            ['youtube url', 'URL do YouTube'],
            ['linkedin url', 'URL do LinkedIn']
        ];

        const MAPA_PALAVRAS = {
            client: 'cliente',
            app: 'aplicativo',
            secret: 'segredo',
            key: 'chave',
            access: 'acesso',
            refresh: 'atualizacao',
            token: 'token',
            username: 'usuario',
            user: 'usuario',
            users: 'usuarios',
            email: 'e-mail',
            password: 'senha',
            confirm: 'confirmar',
            confirmation: 'confirmacao',
            phone: 'telefone',
            mobile: 'celular',
            address: 'endereco',
            city: 'cidade',
            state: 'estado',
            country: 'pais',
            postal: 'postal',
            zip: 'cep',
            title: 'titulo',
            description: 'descricao',
            message: 'mensagem',
            code: 'codigo',
            coupon: 'cupom',
            image: 'imagem',
            file: 'arquivo',
            upload: 'envio',
            amount: 'valor',
            price: 'preco',
            number: 'numero',
            date: 'data',
            time: 'hora',
            category: 'categoria',
            status: 'status',
            role: 'perfil',
            plan: 'plano',
            template: 'modelo',
            tracking: 'rastreamento',
            host: 'servidor',
            port: 'porta',
            database: 'banco',
            db: 'banco',
            name: 'nome',
            id: 'ID',
            api: 'API',
            url: 'URL',
            slug: 'slug',
            slide: 'slide'
        };

        const REGEX_TERMO_INGLES = /\b(type|search|select|choose|full|first|last|name|user|username|email|password|confirm|phone|mobile|address|city|state|country|zip|postal|title|description|message|code|coupon|image|file|upload|price|amount|number|date|time|client|app|secret|key|token|template|tracking|host|database|slide)\b/i;

        function normalizarTexto(texto) {
            if (!texto) return '';

            return String(texto)
                .replace(/\s+/g, ' ')
                .replace(/\s*\*+\s*$/g, '')
                .replace(/\(\s*optional\s*\)/ig, '')
                .replace(/\(\s*opcional\s*\)/ig, '')
                .trim();
        }

        function escaparRegex(texto) {
            return texto.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function primeiraMaiuscula(texto) {
            if (!texto) return '';
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function removerAcentos(texto) {
            return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function parecePtBr(texto) {
            const frase = removerAcentos(normalizarTexto(texto).toLowerCase());
            if (!frase) return false;
            return PALAVRAS_PTBR.some(function (palavra) {
                return removerAcentos(palavra).toLowerCase() && frase.includes(removerAcentos(palavra).toLowerCase());
            });
        }

        function traduzirPorFrases(texto) {
            let resultado = normalizarTexto(texto);

            MAPA_FRASES.forEach(function (item) {
                const regex = new RegExp('\\b' + escaparRegex(item[0]) + '\\b', 'gi');
                resultado = resultado.replace(regex, item[1]);
            });

            return resultado;
        }

        function traduzirPorPalavras(texto) {
            return texto.replace(/[A-Za-z][A-Za-z0-9_-]*/g, function (token) {
                const chave = token.toLowerCase();
                return Object.prototype.hasOwnProperty.call(MAPA_PALAVRAS, chave) ? MAPA_PALAVRAS[chave] : token;
            });
        }

        function traduzirParaPtBr(texto) {
            let resultado = normalizarTexto(texto);
            if (!resultado) return '';

            resultado = traduzirPorFrases(resultado);
            resultado = traduzirPorPalavras(resultado);
            resultado = resultado.replace(/^example:\s*/i, 'Ex.: ');
            resultado = resultado.replace(/^ex:\s*/i, 'Ex.: ');
            resultado = resultado.replace(/\s{2,}/g, ' ').trim();

            return resultado;
        }

        function textoNomeCampo(elemento) {
            const nome = (elemento.getAttribute('name') || '').trim();
            if (!nome) return '';

            return normalizarTexto(
                nome
                    .replace(/\[\]$/g, '')
                    .replace(/\[[^\]]*\]/g, ' ')
                    .replace(/[_\-]+/g, ' ')
            );
        }

        function textoLabelPorFor(elemento) {
            const id = elemento.id;
            if (!id || !window.CSS || typeof window.CSS.escape !== 'function') return '';

            const label = document.querySelector('label[for="' + window.CSS.escape(id) + '"]');
            return label ? normalizarTexto(label.textContent) : '';
        }

        function textoLabelProximo(elemento) {
            const labelPai = elemento.closest('label');
            if (labelPai) {
                const texto = normalizarTexto(labelPai.textContent);
                if (texto) return texto;
            }

            const seletores = ['.form-group', '.form-field', '.field', '.input-group', '.mb-2', '.mb-3', '.mb-4'];
            for (const seletor of seletores) {
                const caixa = elemento.closest(seletor);
                if (!caixa) continue;
                const label = caixa.querySelector('label');
                if (!label) continue;
                const texto = normalizarTexto(label.textContent);
                if (texto) return texto;
            }

            return '';
        }

        function textoBaseCampo(elemento) {
            const aria = normalizarTexto(elemento.getAttribute('aria-label') || '');
            if (aria) return aria;

            const porFor = textoLabelPorFor(elemento);
            if (porFor) return porFor;

            const proximo = textoLabelProximo(elemento);
            if (proximo) return proximo;

            return textoNomeCampo(elemento);
        }

        function placeholderPadraoPorTipo(elemento) {
            const tipo = (elemento.getAttribute('type') || 'text').toLowerCase();
            if (tipo === 'email') return 'Digite seu e-mail';
            if (tipo === 'password') return 'Digite sua senha';
            if (tipo === 'url') return 'https://seusite.com.br';
            if (tipo === 'tel') return '(00) 00000-0000';
            if (tipo === 'search') return 'Pesquise aqui';
            if (tipo === 'date') return 'dd/mm/aaaa';
            if (tipo === 'time') return 'hh:mm';
            if (tipo === 'datetime-local') return 'dd/mm/aaaa hh:mm';
            if (tipo === 'number') return 'Digite um valor';
            if (elemento.tagName.toLowerCase() === 'textarea') return 'Digite sua mensagem';
            return 'Digite aqui';
        }

        function ehExemploTecnico(texto) {
            return /^https?:\/\//i.test(texto)
                || /^[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}$/i.test(texto)
                || /^\/[^\s]*$/.test(texto)
                || /^(\d{1,4}[./-]){1,3}\d{1,4}$/.test(texto)
                || /^[A-Z0-9_-]{4,}$/.test(texto);
        }

        function montarPlaceholderFinal(elemento, base) {
            const texto = normalizarTexto(base);
            const tipo = (elemento.getAttribute('type') || 'text').toLowerCase();
            if (!texto) return placeholderPadraoPorTipo(elemento);

            if (tipo === 'date' || tipo === 'time' || tipo === 'datetime-local') {
                return placeholderPadraoPorTipo(elemento);
            }

            if (/^ex\.\s*:/i.test(texto)) return texto;
            if (ehExemploTecnico(texto)) return 'Ex.: ' + texto;

            const minusculo = texto.toLowerCase();

            if (minusculo.startsWith('digite ') || minusculo.startsWith('informe ') || minusculo.startsWith('selecione ') || minusculo.startsWith('escolha ') || minusculo.startsWith('pesquise ')) {
                return primeiraMaiuscula(texto);
            }

            if (tipo === 'search') {
                return 'Pesquisar ' + minusculo;
            }

            return 'Digite ' + minusculo;
        }

        function deveTratarInputOuTextarea(elemento) {
            if (!(elemento instanceof HTMLElement)) return false;
            if (elemento.matches('[data-no-auto-placeholder], [data-no-auto-placeholder="1"]')) return false;

            const tag = elemento.tagName.toLowerCase();
            if (tag === 'textarea') return true;
            if (tag !== 'input') return false;

            const tipo = (elemento.getAttribute('type') || 'text').toLowerCase();
            return !TIPOS_IGNORADOS.has(tipo);
        }

        function placeholderAtual(elemento) {
            return normalizarTexto(elemento.getAttribute('placeholder') || '');
        }

        function dataPlaceholderAtual(elemento) {
            return normalizarTexto(elemento.getAttribute('data-placeholder') || '');
        }

        function gerarPlaceholderPtBr(elemento) {
            const atual = placeholderAtual(elemento);
            let base = traduzirParaPtBr(atual);

            if (!base) {
                base = traduzirParaPtBr(textoBaseCampo(elemento));
            }

            if (base && !parecePtBr(base) && REGEX_TERMO_INGLES.test(base)) {
                base = traduzirParaPtBr(base);
            }

            return montarPlaceholderFinal(elemento, base);
        }

        function aplicarPlaceholderInputTextarea(elemento) {
            if (!deveTratarInputOuTextarea(elemento)) return;

            const novo = gerarPlaceholderPtBr(elemento);
            if (!novo) return;

            if (placeholderAtual(elemento) !== normalizarTexto(novo)) {
                elemento.setAttribute('placeholder', novo);
                elemento.setAttribute('data-auto-placeholder', '1');
            }
        }

        function aplicarDataPlaceholder(elemento) {
            if (!(elemento instanceof HTMLElement)) return;
            if (elemento.matches('[data-no-auto-placeholder], [data-no-auto-placeholder="1"]')) return;
            if (!elemento.hasAttribute('data-placeholder')) return;

            const atual = dataPlaceholderAtual(elemento);
            if (!atual) return;

            let novo = traduzirParaPtBr(atual);
            if (!novo) return;

            if (/^select\b/i.test(novo)) {
                novo = novo.replace(/^select\b/i, 'Selecione');
            }
            if (/^choose\b/i.test(novo)) {
                novo = novo.replace(/^choose\b/i, 'Escolha');
            }

            if (atual !== normalizarTexto(novo)) {
                elemento.setAttribute('data-placeholder', novo);
                elemento.setAttribute('data-auto-placeholder', '1');
            }
        }

        function aplicarEmContainer(container) {
            if (!container || !(container instanceof HTMLElement || container instanceof Document)) return;

            if (container instanceof HTMLElement) {
                aplicarPlaceholderInputTextarea(container);
                aplicarDataPlaceholder(container);
            }

            container.querySelectorAll('input, textarea').forEach(aplicarPlaceholderInputTextarea);
            container.querySelectorAll('[data-placeholder]').forEach(aplicarDataPlaceholder);
        }

        function iniciarPadronizacaoPlaceholdersPtBr() {
            aplicarEmContainer(document);

            if (window.jQuery) {
                window.jQuery(document).on('shown.bs.modal pjax:end ajaxComplete', function () {
                    aplicarEmContainer(document);
                });
            }

            if (!window.MutationObserver || !document.body) return;

            const observador = new MutationObserver(function (mutacoes) {
                for (const mutacao of mutacoes) {
                    mutacao.addedNodes.forEach(function (node) {
                        if (!(node instanceof HTMLElement)) return;
                        aplicarEmContainer(node);
                    });
                }
            });

            observador.observe(document.body, { childList: true, subtree: true });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', iniciarPadronizacaoPlaceholdersPtBr);
        } else {
            iniciarPadronizacaoPlaceholdersPtBr();
        }
    })();
</script>
