(function () {
    'use strict';

    /**
     * Aplica mascaras brasileiras padronizadas (CPF, CNPJ, RG, Moeda, Data, Hora, Telefone, CEP)
     * e auto-preenchimento de endereco via ViaCEP no 8o digito com foco direto no campo Numero.
     */
    function applyVanillaRegexMask(el, type) {
        if (!el || el.dataset.unnVanillaMaskBound === '1') return;
        el.dataset.unnVanillaMaskBound = '1';

        el.addEventListener('input', function () {
            const raw = el.value;
            const digits = raw.replace(/\D/g, '');

            if (type === 'cep') {
                const d = digits.slice(0, 8);
                el.value = d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
            } else if (type === 'cpf') {
                const d = digits.slice(0, 11);
                el.value = d
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else if (type === 'cnpj') {
                const d = digits.slice(0, 14);
                el.value = d
                    .replace(/^(\d{2})(\d)/, '$1.$2')
                    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                    .replace(/\.(\d{3})(\d)/, '.$1/$2')
                    .replace(/(\d{4})(\d)/, '$1-$2');
            } else if (type === 'rg') {
                const alnum = raw.replace(/[^0-9Xx]/g, '').toUpperCase().slice(0, 9);
                if (alnum.length <= 8) {
                    el.value = alnum
                        .replace(/^(\d{1,2})(\d{3})(\d{3})$/, '$1.$2.$3')
                        .replace(/^(\d{2})(\d)/, '$1.$2')
                        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                } else {
                    el.value = alnum.slice(0, 2) + '.' + alnum.slice(2, 5) + '.' + alnum.slice(5, 8) + '-' + alnum.slice(8);
                }
            } else if (type === 'phone') {
                const d = digits.slice(0, 11);
                if (d.length <= 10) {
                    el.value = d
                        .replace(/^(\d{2})(\d)/, '($1) $2')
                        .replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    el.value = d
                        .replace(/^(\d{2})(\d)/, '($1) $2')
                        .replace(/(\d{5})(\d)/, '$1-$2');
                }
            } else if (type === 'date') {
                const d = digits.slice(0, 8);
                el.value = d
                    .replace(/^(\d{2})(\d)/, '$1/$2')
                    .replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
            } else if (type === 'time') {
                const d = digits.slice(0, 4);
                el.value = d.replace(/^(\d{2})(\d)/, '$1:$2');
            }
        });
    }

    function notify(type, message) {
        if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
            toastr[type](message);
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type === 'error' ? 'error' : (type === 'info' ? 'info' : 'success'),
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
    }

    function setFieldValue(scope, selectors, value) {
        for (const selector of selectors) {
            const el = scope.querySelector(selector);
            if (el) {
                el.value = value || '';
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                return el;
            }
        }
        return null;
    }

    function lookupViaCep(input) {
        if (!input) return;
        const cep = (input.value || '').replace(/\D/g, '');
        if (cep.length !== 8) return;
        if (input.dataset.lastCepFetched === cep) return;
        input.dataset.lastCepFetched = cep;

        const scope = input.closest('form') || document;
        const targetNumber = input.dataset.targetNumber;
        const targetComplement = input.dataset.targetComplement;
        const targetDistrict = input.dataset.targetDistrict;
        const targetAddress = input.dataset.targetAddress;
        const targetCity = input.dataset.targetCity;
        const targetState = input.dataset.targetState;

        notify('info', 'Consultando CEP...');

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then((r) => r.json())
            .then((data) => {
                if (!data || data.erro) {
                    notify('error', 'CEP nao encontrado.');
                    return;
                }

                const addressSelectors = [
                    targetAddress,
                    '[name="company_address"]',
                    '[name="address"]',
                    '[name="logradouro"]',
                    '[name="street"]',
                    '#address',
                    '#logradouro'
                ].filter(Boolean);

                const districtSelectors = [
                    targetDistrict,
                    '[name="company_district"]',
                    '[name="neighborhood"]',
                    '[name="district"]',
                    '[name="bairro"]',
                    '#neighborhood',
                    '#bairro'
                ].filter(Boolean);

                const citySelectors = [
                    targetCity,
                    '[name="company_city"]',
                    '[name="city"]',
                    '[name="localidade"]',
                    '[name="cidade"]',
                    '#city',
                    '#cidade'
                ].filter(Boolean);

                const stateSelectors = [
                    targetState,
                    '[name="company_state"]',
                    '[name="state"]',
                    '[name="uf"]',
                    '[name="estado"]',
                    '#state',
                    '#uf'
                ].filter(Boolean);

                const numberSelectors = [
                    targetNumber,
                    '[name="number"]',
                    '[name="numero"]',
                    '[name="address_number"]',
                    '[name="company_number"]',
                    '#number',
                    '#numero'
                ].filter(Boolean);

                const addressEl = scope.querySelector(addressSelectors[0]) || setFieldValue(scope, addressSelectors, '');
                if (addressEl && !scope.querySelector(districtSelectors.join(',')) && !scope.querySelector(citySelectors.join(','))) {
                    // Formulario compacto de campo unico (`#address`)
                    const parts = [data.logradouro, data.bairro, data.localidade && data.uf ? data.localidade + '/' + data.uf : (data.localidade || '')].filter(Boolean);
                    setFieldValue(scope, addressSelectors, parts.join(' - '));
                } else {
                    setFieldValue(scope, addressSelectors, data.logradouro || '');
                    setFieldValue(scope, districtSelectors, data.bairro || '');
                    setFieldValue(scope, citySelectors, data.localidade || '');
                    setFieldValue(scope, stateSelectors, data.uf || '');
                }

                notify('success', 'Endereco preenchido automaticamente!');

                for (const sel of numberSelectors) {
                    const numEl = scope.querySelector(sel) || document.querySelector(sel);
                    if (numEl && !numEl.disabled && numEl.offsetParent !== null) {
                        numEl.focus();
                        if (typeof numEl.select === 'function') numEl.select();
                        return;
                    }
                }

                if (targetComplement) {
                    const compEl = scope.querySelector(targetComplement) || document.querySelector(targetComplement);
                    if (compEl) compEl.focus();
                }
            })
            .catch(() => {
                notify('error', 'Nao foi possivel consultar o ViaCEP.');
            });
    }

    function bindCepElement(el) {
        if (!el || el.dataset.unnCepBound === '1') return;
        el.dataset.unnCepBound = '1';

        let timer = null;
        el.addEventListener('input', function () {
            const cep = (el.value || '').replace(/\D/g, '');
            if (cep.length === 8) {
                clearTimeout(timer);
                timer = setTimeout(() => lookupViaCep(el), 180);
            }
        });
        el.addEventListener('blur', function () {
            lookupViaCep(el);
        });
    }

    window.initUnnMasks = function (root) {
        const scope = root && root.querySelectorAll ? root : document;
        const hasInputmask = typeof window.Inputmask === 'function';
        const hasJQueryMask = typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.inputmask === 'function';

        const applyMask = function (el, patternOrOptions, fallbackType) {
            if (!el || el.dataset.unnMaskApplied === '1') return;
            el.dataset.unnMaskApplied = '1';

            if (hasJQueryMask) {
                if (typeof patternOrOptions === 'string') {
                    window.jQuery(el).inputmask(patternOrOptions);
                } else if (patternOrOptions && patternOrOptions.alias) {
                    window.jQuery(el).inputmask(patternOrOptions.alias, patternOrOptions.options || {});
                } else {
                    window.jQuery(el).inputmask(patternOrOptions);
                }
            } else if (hasInputmask) {
                if (typeof patternOrOptions === 'string') {
                    window.Inputmask(patternOrOptions).mask(el);
                } else if (patternOrOptions && patternOrOptions.alias) {
                    window.Inputmask(patternOrOptions.alias, patternOrOptions.options || {}).mask(el);
                } else {
                    window.Inputmask(patternOrOptions).mask(el);
                }
            } else if (fallbackType) {
                applyVanillaRegexMask(el, fallbackType);
            }
        };

        scope.querySelectorAll('.mask-cep, [data-mask="cep"], input#cep').forEach((el) => {
            applyMask(el, '99999-999', 'cep');
            bindCepElement(el);
        });
        scope.querySelectorAll('.mask-cpf, [data-mask="cpf"]').forEach((el) => applyMask(el, '999.999.999-99', 'cpf'));
        scope.querySelectorAll('.mask-cnpj, [data-mask="cnpj"]').forEach((el) => applyMask(el, '99.999.999/9999-99', 'cnpj'));
        scope.querySelectorAll('.mask-cpf-cnpj, [data-mask="cpf-cnpj"]').forEach((el) => {
            applyMask(el, { mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true, placeholder: '_' }, 'cpf');
        });
        scope.querySelectorAll('.mask-rg, [data-mask="rg"]').forEach((el) => {
            applyMask(el, {
                mask: ['99.999.999-*', '9.999.999-*'],
                keepStatic: true,
                definitions: { '*': { validator: '[0-9Xx]' } }
            }, 'rg');
        });
        scope.querySelectorAll('.mask-date, [data-mask="date"]').forEach((el) => applyMask(el, '99/99/9999', 'date'));
        scope.querySelectorAll('.mask-datetime, [data-mask="datetime"]').forEach((el) => applyMask(el, '99/99/9999 99:99', 'date'));
        scope.querySelectorAll('.mask-time, [data-mask="time"]').forEach((el) => applyMask(el, '99:99', 'time'));
        scope.querySelectorAll('.mask-phone, [data-mask="phone"], [data-mask="telefone"]').forEach((el) => {
            applyMask(el, '(99) 9999[9]-9999', 'phone');
        });
        scope.querySelectorAll('.mask-money, [data-mask="money"], [data-mask="currency"]').forEach((el) => {
            applyMask(el, {
                alias: 'currency',
                options: {
                    prefix: 'R$ ',
                    radixPoint: ',',
                    groupSeparator: '.',
                    autoGroup: true,
                    digits: 2,
                    rightAlign: false,
                    substituteRadixPoint: true,
                    onBeforeMask: function (value) {
                        if (value === null || value === undefined) return value;
                        value = String(value);
                        if (value.includes(',') || !value.includes('.')) return value;
                        if (/^\d+\.\d{1,2}$/.test(value)) return value.replace('.', ',');
                        return value;
                    }
                }
            }, null);
        });

        // Suporte a mascaras explicitas legadas data-mask="999.999..."
        scope.querySelectorAll('input[data-mask]').forEach((el) => {
            if (el.dataset.unnMaskApplied === '1') return;
            const m = (el.getAttribute('data-mask') || '').trim();
            if (!m) return;
            applyMask(el, m, null);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.initUnnMasks(document));
    } else {
        window.initUnnMasks(document);
    }
})();
