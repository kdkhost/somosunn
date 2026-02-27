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

        const PALAVRAS_PORTUGUES = [
            'digite', 'informe', 'nome', 'sobrenome', 'apelido', 'usuario', 'e-mail', 'email', 'senha', 'confirmar',
            'telefone', 'celular', 'endereco', 'bairro', 'cidade', 'estado', 'pais', 'cep', 'titulo', 'descricao',
            'mensagem', 'codigo', 'cupom', 'valor', 'preco', 'data', 'hora', 'buscar', 'pesquisar', 'opcional', 'ex'
        ];

        const MAPA_FRASES_INGLES = [
            ['type here', 'Digite aqui'],
            ['search here', 'Pesquise aqui'],
            ['search', 'Pesquisar'],
            ['full name', 'Nome completo'],
            ['first name', 'Nome'],
            ['last name', 'Sobrenome'],
            ['user name', 'Nome de usuario'],
            ['username', 'Nome de usuario'],
            ['email address', 'E-mail'],
            ['email', 'E-mail'],
            ['password confirmation', 'Confirmacao de senha'],
            ['confirm password', 'Confirmar senha'],
            ['password', 'Senha'],
            ['phone number', 'Telefone'],
            ['phone', 'Telefone'],
            ['mobile', 'Celular'],
            ['address', 'Endereco'],
            ['city', 'Cidade'],
            ['state', 'Estado'],
            ['country', 'Pais'],
            ['zip code', 'CEP'],
            ['postal code', 'CEP'],
            ['title', 'Titulo'],
            ['description', 'Descricao'],
            ['message', 'Mensagem'],
            ['code', 'Codigo'],
            ['coupon', 'Cupom'],
            ['url', 'URL'],
            ['link', 'Link'],
            ['image', 'Imagem'],
            ['file', 'Arquivo'],
            ['upload', 'Envio'],
            ['price', 'Preco'],
            ['amount', 'Valor'],
            ['number', 'Numero'],
            ['date', 'Data'],
            ['time', 'Hora']
        ];

        function escaparRegex(texto) {
            return texto.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function normalizarTexto(texto) {
            if (!texto) return '';

            return String(texto)
                .replace(/\s+/g, ' ')
                .replace(/\s*\*+\s*$/g, '')
                .replace(/\(\s*optional\s*\)/ig, '')
                .replace(/\(\s*opcional\s*\)/ig, '')
                .trim();
        }

        function primeiraMaiuscula(texto) {
            if (!texto) return '';
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function parecePortugues(texto) {
            const frase = normalizarTexto(texto).toLowerCase();
            if (!frase) return false;
            if (/[áàâãéêíóôõúç]/i.test(frase)) return true;
            return PALAVRAS_PORTUGUES.some(function (palavra) {
                return frase.includes(palavra);
            });
        }

        function traduzirParaPortugues(texto) {
            let resultado = normalizarTexto(texto);
            if (!resultado) return '';

            MAPA_FRASES_INGLES.forEach(function (item) {
                const origem = item[0];
                const destino = item[1];
                const regex = new RegExp('\\b' + escaparRegex(origem) + '\\b', 'gi');
                resultado = resultado.replace(regex, destino);
            });

            resultado = resultado.replace(/^ex:\s*/i, 'Ex.: ');
            resultado = resultado.replace(/^example:\s*/i, 'Ex.: ');

            return normalizarTexto(resultado);
        }

        function textoDoNomeCampo(elemento) {
            const nome = (elemento.getAttribute('name') || '').trim();
            if (!nome) return '';

            const limpo = nome
                .replace(/\[\]$/g, '')
                .replace(/\[[^\]]*\]/g, ' ')
                .replace(/[_\-]+/g, ' ');

            return normalizarTexto(limpo);
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
                if (label) {
                    const texto = normalizarTexto(label.textContent);
                    if (texto) return texto;
                }
            }

            return '';
        }

        function textoBaseCampo(elemento) {
            const aria = normalizarTexto(elemento.getAttribute('aria-label') || '');
            if (aria) return aria;

            const labelFor = textoLabelPorFor(elemento);
            if (labelFor) return labelFor;

            const labelProximo = textoLabelProximo(elemento);
            if (labelProximo) return labelProximo;

            return textoDoNomeCampo(elemento);
        }

        function placeholderPorTipo(elemento) {
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

            if (elemento.tagName.toLowerCase() === 'textarea') {
                return 'Digite sua mensagem';
            }

            return 'Digite aqui';
        }

        function placeholderFinal(elemento, textoBase) {
            const tipo = (elemento.getAttribute('type') || 'text').toLowerCase();
            const base = normalizarTexto(textoBase);
            if (!base) return placeholderPorTipo(elemento);

            const baseMinuscula = base.toLowerCase();

            if (tipo === 'date' || tipo === 'time' || tipo === 'datetime-local') {
                return placeholderPorTipo(elemento);
            }

            if (/^https?:\/\//i.test(base) || /^ex\.\s*:/i.test(base) || /^ex:\s*/i.test(base)) {
                return base.replace(/^ex:\s*/i, 'Ex.: ');
            }

            if (baseMinuscula.startsWith('digite ') || baseMinuscula.startsWith('informe ') || baseMinuscula.startsWith('pesquise ')) {
                return primeiraMaiuscula(base);
            }

            if (baseMinuscula.startsWith('selecione ')) {
                return primeiraMaiuscula(base);
            }

            if (tipo === 'search') {
                return 'Pesquise ' + baseMinuscula;
            }

            return 'Digite ' + baseMinuscula;
        }

        function deveTratar(elemento) {
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

        function gerarPlaceholderPortugues(elemento) {
            const atual = placeholderAtual(elemento);
            const precisaTraduzirAtual = atual !== '' && !parecePortugues(atual);

            let base = '';
            if (atual !== '' && !precisaTraduzirAtual) {
                base = atual;
            } else if (atual !== '' && precisaTraduzirAtual) {
                base = traduzirParaPortugues(atual);
            }

            if (!base) {
                base = traduzirParaPortugues(textoBaseCampo(elemento));
            }

            return placeholderFinal(elemento, base);
        }

        function aplicarPlaceholder(elemento) {
            if (!deveTratar(elemento)) return;

            const novoPlaceholder = gerarPlaceholderPortugues(elemento);
            if (!novoPlaceholder) return;

            if (placeholderAtual(elemento) !== novoPlaceholder) {
                elemento.setAttribute('placeholder', novoPlaceholder);
                elemento.setAttribute('data-auto-placeholder', '1');
            }
        }

        function aplicarEmContainer(container) {
            if (!container || !(container instanceof HTMLElement || container instanceof Document)) return;

            if (container instanceof HTMLElement) {
                aplicarPlaceholder(container);
            }

            const campos = container.querySelectorAll('input, textarea');
            campos.forEach(aplicarPlaceholder);
        }

        function iniciarPlaceholdersEmPortugues() {
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
            document.addEventListener('DOMContentLoaded', iniciarPlaceholdersEmPortugues);
        } else {
            iniciarPlaceholdersEmPortugues();
        }
    })();
</script>
