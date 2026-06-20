@php
    $lgpdConsent = $lgpdConsent ?? null;
@endphp

@if(($lgpdConsent['requires_consent'] ?? false) === true)
    <div id="lgpd-consent-modal" class="fixed inset-0 z-[10050] flex items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm">
        <div class="absolute inset-0"></div>
        <div class="relative z-[1] w-full max-w-3xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_30px_90px_rgba(15,23,42,0.38)]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-blue-600 via-cyan-600 to-blue-700 px-6 py-5 text-white sm:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100">Consentimento obrigatório</p>
                        <h2 class="mt-2 text-2xl font-black leading-tight">Aceite os termos de LGPD para continuar usando a plataforma</h2>
                    </div>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-blue-50">
                        versão {{ substr((string) ($lgpdConsent['version'] ?? ''), 0, 12) }}
                    </span>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-6 sm:px-8">
                <p class="text-sm leading-6 text-slate-600">
                    Antes de prosseguir, confirme que você leu e concorda com os documentos jurídicos abaixo. O acesso continua bloqueado até o registro do aceite.
                </p>

                <div class="mt-6 space-y-4">
                    @foreach(($lgpdConsent['documents'] ?? []) as $document)
                        <article class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">{{ $document['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $document['summary'] }}</p>
                                    @if(!empty($document['updated_label']))
                                        <p class="mt-3 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
                                            Atualizado em {{ $document['updated_label'] }}
                                        </p>
                                    @endif
                                </div>
                                <a href="{{ $document['url'] }}" target="_blank" rel="noopener"
                                    class="inline-flex shrink-0 items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-600 hover:text-blue-700">
                                    Abrir documento
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <form id="lgpd-consent-form" action="{{ route('lgpd.accept') }}" method="POST"
                    class="mt-6 rounded-[1.4rem] border border-blue-100 bg-blue-50 p-4 text-sm text-slate-700">
                    @csrf
                    <label class="flex cursor-pointer items-start gap-3">
                        <input id="lgpd-consent-checkbox" name="accept" value="1" type="checkbox" required
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            Li e aceito os Termos de Uso, a Política de Privacidade e o Consentimento LGPD, autorizando o tratamento dos meus dados conforme descrito nesses documentos.
                        </span>
                    </label>
                    <p id="lgpd-consent-error" class="mt-3 hidden text-sm font-semibold text-rose-600"></p>
                </form>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form id="lgpd-logout-form" action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-white sm:w-auto">
                            Sair da conta
                        </button>
                    </form>

                    <button type="submit" form="lgpd-consent-form" id="lgpd-consent-submit"
                        class="inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-700 sm:w-auto">
                        Aceitar e continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('lgpd-consent-modal');
            const form = document.getElementById('lgpd-consent-form');
            const checkbox = document.getElementById('lgpd-consent-checkbox');
            const submitButton = document.getElementById('lgpd-consent-submit');
            const error = document.getElementById('lgpd-consent-error');
            const csrf = document.querySelector('meta[name="csrf-token"]');

            if (!modal || !form || !checkbox || !submitButton || !csrf) {
                return;
            }

            document.body.classList.add('overflow-hidden');

            const setError = function (message) {
                if (!message) {
                    error.textContent = '';
                    error.classList.add('hidden');
                    return;
                }

                error.textContent = message;
                error.classList.remove('hidden');
            };

            const syncButtonState = function () {
                if (checkbox.checked) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('bg-slate-300');
                    submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
                } else {
                    submitButton.disabled = true;
                    submitButton.classList.add('bg-slate-300');
                    submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                }
            };

            checkbox.addEventListener('change', function () {
                setError('');
                syncButtonState();
            });

            form.addEventListener('submit', async function (event) {
                if (!checkbox.checked || submitButton.disabled) {
                    event.preventDefault();
                    setError('Marque o aceite para continuar.');
                    return;
                }

                if (typeof window.fetch !== 'function') {
                    return;
                }

                event.preventDefault();
                submitButton.disabled = true;
                submitButton.textContent = 'Registrando aceite...';
                setError('');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf.getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ accept: true }),
                    });

                    const payload = await response.json().catch(function () {
                        return {};
                    });

                    if (!response.ok) {
                        throw new Error(payload.message || 'Nao foi possivel registrar o aceite.');
                    }

                    window.location.reload();
                } catch (exception) {
                    setError(exception.message || 'Nao foi possivel registrar o aceite.');
                    submitButton.textContent = 'Aceitar e continuar';
                    syncButtonState();
                }
            });

            syncButtonState();
        })();
    </script>
@endif
