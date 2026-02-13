@extends('panel.layouts.app')

@section('title', 'Meu Perfil - UNN')

@section('panel_content')
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Meu perfil</h1>
                <p class="text-slate-600 mt-1">Mantenha seus dados atualizados para melhor experiência na plataforma.</p>
            </div>
            <a href="{{ route('panel.dashboard') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nome completo *</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required
                        placeholder="Digite seu nome completo" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        placeholder="seu@email.com" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Telefone</label>
                    <input id="profile_phone" name="phone" value="{{ old('phone', $user->phone) }}"
                        data-mask-phone maxlength="16" inputmode="tel" autocomplete="tel"
                        placeholder="(99) 99999-9999" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Documento</label>
                    <div class="flex gap-2">
                        <select id="doc_type" name="doc_type" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" style="min-width:110px">
                            <option value="cpf" {{ (old('doc_type', (strlen(preg_replace('/\D/', '', $user->doc ?? '')) <= 11 ? 'cpf' : 'cnpj')) == 'cpf') ? 'selected' : '' }}>CPF</option>
                            <option value="cnpj" {{ (old('doc_type', (strlen(preg_replace('/\D/', '', $user->doc ?? '')) > 11 ? 'cnpj' : 'cpf')) == 'cnpj') ? 'selected' : '' }}>CNPJ</option>
                        </select>
                        <input id="profile_doc" name="doc" value="{{ old('doc', $user->doc) }}"
                            maxlength="18" inputmode="numeric" autocomplete="off"
                            placeholder="Digite o número" title="Digite apenas números. Aceita {{ old('doc_type', (strlen(preg_replace('/\D/', '', $user->doc ?? '')) <= 11 ? 'CPF' : 'CNPJ')) }} válido."
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    </div>
                    <small class="text-xs text-slate-500">Selecione o tipo e digite o número do documento.</small>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Ocupação</label>
                    <input name="occupation" value="{{ old('occupation', $user->occupation) }}"
                        placeholder="Sua ocupação" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Empresa</label>
                    <input name="company" value="{{ old('company', $user->company) }}"
                        placeholder="Empresa onde trabalha" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Segmento</label>
                    <input name="segment" value="{{ old('segment', $user->segment) }}"
                        placeholder="Segmento de atuação" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 mb-1 block">Áreas de Interesse</label>
                    @php
                        $opcoesInteresses = [
                            'Marketing', 'Tecnologia', 'Saúde', 'Educação', 'Finanças', 'Gestão', 'Design', 'Engenharia', 'Direito', 'Psicologia', 'RH', 'Vendas', 'Comunicação', 'Outro'
                        ];
                        $interessesSelecionados = collect(old('interests', $user->interests ?? []))->filter()->map(fn($i) => trim($i));
                    @endphp
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($opcoesInteresses as $opcao)
                            <label class="inline-flex items-center px-3 py-2 bg-slate-100 rounded-xl cursor-pointer text-sm font-medium transition hover:bg-blue-100">
                                <input type="checkbox" name="interests[]" value="{{ $opcao }}" class="form-checkbox accent-blue-600 mr-2 interesse-checkbox" data-interesse="{{ $opcao }}" {{ $interessesSelecionados->contains($opcao) ? 'checked' : '' }}>
                                {{ $opcao }}
                            </label>
                        @endforeach
                        <input type="text" name="interest_other" id="interest-other-input" placeholder="Descreva seu outro interesse" class="mt-2 w-full rounded-2xl border border-blue-200 px-4 py-3 text-sm bg-white hidden" value="{{ old('interest_other', $user->interest_other ?? '') }}">
                    </div>
                    <small class="text-xs text-slate-500 block mt-1">Selecione uma ou mais áreas. Você pode marcar quantas quiser.</small>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            function toggleOtherField() {
                                const outro = document.querySelector('input.interesse-checkbox[data-interesse="Outro"]');
                                const input = document.getElementById('interest-other-input');
                                if (outro && input) {
                                    input.classList.toggle('hidden', !outro.checked);
                                }
                            }
                            document.querySelectorAll('input.interesse-checkbox').forEach(cb => {
                                cb.addEventListener('change', toggleOtherField);
                            });
                            toggleOtherField();
                        });
                    </script>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bio</label>
                    <textarea name="bio" rows="4"
                        placeholder="Conte um pouco sobre você" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nova senha</label>
                    <input type="password" name="password" minlength="6"
                        placeholder="Nova senha" title="Mínimo 6 caracteres. Use letras e números para maior segurança." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p class="text-xs text-slate-500 mt-2">Deixe em branco para manter a senha atual.</p>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Confirmar senha</label>
                    <input type="password" name="password_confirmation" minlength="6"
                        placeholder="Confirme a nova senha" title="Repita a senha para confirmação." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-image text-slate-500"></i> Fotos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <div>
                    <div class="text-sm font-bold text-slate-700">Foto de perfil</div>
                    <div class="mt-3 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
                            <img src="{{ $user->profile_photo_url }}" alt="Avatar" class="w-full h-full object-cover" loading="lazy" onerror="this.src='{{ asset('img/default-user.svg') }}'">
                        </div>
                        <input type="file" name="photo" accept="image/*" id="profile_photo_input"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#1F5EDB] file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:brightness-110">
                        <small class="text-xs text-slate-500">Máx. 2MB. Imagens grandes serão rejeitadas.</small>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-bold text-slate-700">Capa</div>
                    <div class="mt-3">
                        @if($user->cover_photo)
                            <div class="w-full h-28 rounded-2xl overflow-hidden bg-slate-100">
                                <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        @endif
                        <input type="file" name="cover_photo" accept="image/*" id="cover_photo_input"
                            class="mt-3 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800">
                        <small class="text-xs text-slate-500">Máx. 4MB. Imagens grandes serão rejeitadas.</small>
                    @push('scripts')
                    <script>
                    // Validação de tamanho de upload de imagem (frontend)
                    document.addEventListener('DOMContentLoaded', function() {
                        var photoInput = document.getElementById('profile_photo_input');
                        var coverInput = document.getElementById('cover_photo_input');
                        if (photoInput) {
                            photoInput.addEventListener('change', function(e) {
                                if (e.target.files[0] && e.target.files[0].size > 2 * 1024 * 1024) {
                                    alert('A foto de perfil deve ter no máximo 2MB.');
                                    e.target.value = '';
                                }
                            });
                        }
                        if (coverInput) {
                            coverInput.addEventListener('change', function(e) {
                                if (e.target.files[0] && e.target.files[0].size > 4 * 1024 * 1024) {
                                    alert('A imagem de capa deve ter no máximo 4MB.');
                                    e.target.value = '';
                                }
                            });
                        }
                    });
                    </script>
                    @endpush
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-slate-500"></i> Endereço
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">CEP</label>
                    <input id="profile_cep" name="cep" value="{{ old('cep', $user->cep) }}"
                        data-mask-cep maxlength="9" inputmode="numeric" autocomplete="postal-code"
                        placeholder="00000-000" title="Digite o CEP para preencher endereço automaticamente." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p id="profile_cep_feedback" class="mt-2 text-xs text-slate-500"></p>
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Rua/Avenida</label>
                    <input id="profile_street" name="street" value="{{ old('street', $user->street) }}"
                        placeholder="Rua/Avenida" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Número</label>
                    <input id="profile_number" name="number" value="{{ old('number', $user->number) }}"
                        placeholder="Número" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Complemento</label>
                    <input id="profile_complement" name="complement" value="{{ old('complement', $user->complement) }}"
                        placeholder="Complemento" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bairro</label>
                    <input id="profile_neighborhood" name="neighborhood" value="{{ old('neighborhood', $user->neighborhood) }}"
                        placeholder="Bairro" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Cidade</label>
                    <input id="profile_city" name="city" value="{{ old('city', $user->city) }}"
                        placeholder="Cidade" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">UF</label>
                    <input id="profile_state" name="state" value="{{ old('state', $user->state) }}" maxlength="2" autocomplete="address-level1"
                        placeholder="UF" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-share-alt text-slate-500"></i> Redes sociais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Website</label>
                    <input name="website" value="{{ old('website', $user->website) }}"
                        placeholder="Seu site ou portfólio" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Instagram</label>
                    <input name="instagram" value="{{ old('instagram', $user->instagram) }}"
                        placeholder="@seuusuario" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Facebook</label>
                    <input name="facebook" value="{{ old('facebook', $user->facebook) }}"
                        placeholder="facebook.com/seuusuario" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Twitter</label>
                    <input name="twitter" value="{{ old('twitter', $user->twitter) }}"
                        placeholder="@seuusuario" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">LinkedIn</label>
                    <input name="linkedin" value="{{ old('linkedin', $user->linkedin) }}"
                        placeholder="linkedin.com/in/seuusuario" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">YouTube</label>
                    <input name="youtube" value="{{ old('youtube', $user->youtube) }}"
                        placeholder="youtube.com/seucanal" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-lock text-slate-500"></i> Privacidade
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_email_public" {{ old('show_email_public', (bool) $user->show_email_public) ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-slate-700">Mostrar e-mail publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_phone_public" {{ old('show_phone_public', (bool) $user->show_phone_public) ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-slate-700">Mostrar telefone publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_address_public" {{ old('show_address_public', (bool) $user->show_address_public) ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-slate-700">Mostrar endereço publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="hide_profile" {{ old('hide_profile', (bool) $user->hide_profile) ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-slate-700">Ocultar meu perfil</span>
                </label>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-8 py-3 text-sm font-extrabold text-white hover:brightness-110 transition">
                <i class="fas fa-save mr-2"></i> Salvar alterações
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            function applyProfileMasks() {
                if (typeof Inputmask !== 'function') {
                    return;
                }

                var cepInput = document.getElementById('profile_cep');
                var phoneInput = document.getElementById('profile_phone');
                var docInput = document.getElementById('profile_doc');
                var docType = document.getElementById('doc_type');

                if (cepInput) {
                    Inputmask('99999-999').mask(cepInput);
                }
                if (phoneInput) {
                    Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(phoneInput);
                }
                if (docInput && docType) {
                    var mask = docType.value === 'cpf' ? '999.999.999-99' : '99.999.999/9999-99';
                    Inputmask(mask).mask(docInput);
                }

                if (docType && docInput) {
                    docType.addEventListener('change', function() {
                        docInput.value = '';
                        var mask = docType.value === 'cpf' ? '999.999.999-99' : '99.999.999/9999-99';
                        Inputmask.remove(docInput);
                        Inputmask(mask).mask(docInput);
                        docInput.placeholder = docType.value === 'cpf' ? 'Digite o CPF' : 'Digite o CNPJ';
                        docInput.title = 'Digite apenas números. Aceita ' + (docType.value === 'cpf' ? 'CPF' : 'CNPJ') + ' válido.';
                    });
                }
            }

            function initViaCepAutofill() {
                var cepInput = document.getElementById('profile_cep');
                if (!cepInput) {
                    return;
                }

                var feedback = document.getElementById('profile_cep_feedback');
                var streetInput = document.getElementById('profile_street');
                var neighborhoodInput = document.getElementById('profile_neighborhood');
                var cityInput = document.getElementById('profile_city');
                var stateInput = document.getElementById('profile_state');
                var lastCep = '';

                function setFeedback(message, isError) {
                    if (!feedback) {
                        return;
                    }

                    feedback.textContent = message || '';
                    feedback.classList.toggle('text-red-600', !!isError);
                    feedback.classList.toggle('text-slate-500', !isError);
                }

                function fetchCep(auto = false) {
                    var digits = (cepInput.value || '').replace(/\D/g, '');
                    if (digits.length !== 8 || digits === lastCep) {
                        return;
                    }

                    lastCep = digits;
                    setFeedback('Buscando endereco...', false);

                    fetch('https://viacep.com.br/ws/' + digits + '/json/')
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (data) {
                            if (!data || data.erro) {
                                setFeedback('CEP nao encontrado. Complete o endereco manualmente.', true);
                                return;
                            }

                            if (streetInput) streetInput.value = data.logradouro || '';
                            if (neighborhoodInput) neighborhoodInput.value = data.bairro || '';
                            if (cityInput) cityInput.value = data.localidade || '';
                            if (stateInput) stateInput.value = (data.uf || '').toUpperCase();

                            setFeedback('Endereco preenchido automaticamente.', false);

                            // Foco automático no campo número
                            var numberInput = document.getElementById('profile_number');
                            if (numberInput) {
                                setTimeout(function() { numberInput.focus(); }, 100);
                            }
                        })
                        .catch(function () {
                            setFeedback('Nao foi possivel consultar o CEP agora. Complete manualmente.', true);
                        });
                }

                // Ao digitar o CEP completo, busca automaticamente
                cepInput.addEventListener('input', function () {
                    var digits = (cepInput.value || '').replace(/\D/g, '');
                    if (digits.length === 8) {
                        fetchCep(true);
                    } else {
                        lastCep = '';
                        setFeedback('', false);
                    }
                });
                // Mantém suporte ao blur
                cepInput.addEventListener('blur', function () {
                    var digits = (cepInput.value || '').replace(/\D/g, '');
                    if (digits.length === 8) fetchCep();
                });
            }

            function initProfileEnhancements() {
                applyProfileMasks();
                initViaCepAutofill();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProfileEnhancements);
            } else {
                initProfileEnhancements();
            }
        })();
    </script>
@endpush
