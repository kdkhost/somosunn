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
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition"
                data-tooltip="Voltar para o painel principal" style="position:relative;">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        {{-- DADOS PESSOAIS --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nome completo *</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required maxlength="80"
                        placeholder="Digite seu nome completo"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="120"
                        placeholder="exemplo@email.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Telefone</label>
                    <input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" inputmode="tel"
                        autocomplete="tel" data-mask-phone placeholder="(99) 99999-9999"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Documento</label>
                    <input name="doc" value="{{ old('doc', $user->doc) }}" maxlength="18" inputmode="numeric"
                        autocomplete="off" data-mask-doc placeholder="CPF ou CNPJ"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Ocupação</label>
                    <input name="occupation" value="{{ old('occupation', $user->occupation) }}" maxlength="60"
                        placeholder="Sua profissão"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Empresa</label>
                    <input name="company" value="{{ old('company', $user->company) }}" maxlength="60"
                        placeholder="Nome da empresa"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Segmento</label>
                    <select name="segment_select" id="segment_select"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                        <option value="">Selecione...</option>
                        @foreach(['Tecnologia', 'Marketing', 'Vendas', 'Saúde', 'Educação', 'Direito', 'Engenharia', 'Finanças', 'Imobiliário', 'Alimentação', 'Consultoria', 'RH'] as $opt)
                            <option value="{{ $opt }}" {{ old('segment', $user->segment) == $opt ? 'selected' : '' }}>{{ $opt }}
                            </option>
                        @endforeach
                        <option value="Outros" {{ !in_array(old('segment', $user->segment), ['Tecnologia', 'Marketing', 'Vendas', 'Saúde', 'Educação', 'Direito', 'Engenharia', 'Finanças', 'Imobiliário', 'Alimentação', 'Consultoria', 'RH', '', null]) ? 'selected' : '' }}>Outros</option>
                    </select>
                    <input name="segment_custom" id="segment_custom"
                        value="{{ !in_array(old('segment', $user->segment), ['Tecnologia', 'Marketing', 'Vendas', 'Saúde', 'Educação', 'Direito', 'Engenharia', 'Finanças', 'Imobiliário', 'Alimentação', 'Consultoria', 'RH', '', null]) ? old('segment', $user->segment) : '' }}"
                        placeholder="Qual seu segmento?"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 {{ !in_array(old('segment', $user->segment), ['Tecnologia', 'Marketing', 'Vendas', 'Saúde', 'Educação', 'Direito', 'Engenharia', 'Finanças', 'Imobiliário', 'Alimentação', 'Consultoria', 'RH', '', null]) ? '' : 'hidden' }}">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 block mb-2">Interesses</label>
                    <div class="flex flex-col gap-2">
                        @php
                            $userInterests = $user->interests ? array_map('trim', explode(',', $user->interests)) : [];
                            $definedInterests = ['Networking', 'Mentorias', 'Cursos', 'Eventos', 'Parcerias', 'Investimentos', 'Vagas', 'Notícias'];
                            $hasCustomInterest = false;
                            foreach ($userInterests as $ui) {
                                if (!in_array($ui, $definedInterests) && !empty($ui))
                                    $hasCustomInterest = true;
                            }
                        @endphp
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($definedInterests as $interest)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="interests_list[]" value="{{ $interest }}" {{ in_array($interest, $userInterests) ? 'checked' : '' }}
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-600">{{ $interest }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 cursor-pointer col-span-2">
                                <input type="checkbox" id="interests_other_cb" {{ $hasCustomInterest ? 'checked' : '' }}
                                    class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-600">Outros</span>
                            </label>
                        </div>
                        <input name="interests_custom" id="interests_custom"
                            value="{{ $hasCustomInterest ? implode(', ', array_diff($userInterests, $definedInterests)) : '' }}"
                            placeholder="Quais outros interesses? (Separe por vírgula)"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $hasCustomInterest ? '' : 'hidden' }}">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bio</label>
                    <textarea name="bio" rows="4" maxlength="500" placeholder="Conte um pouco sobre você"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 border-t border-slate-100 pt-6">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nova senha</label>
                    <input type="password" name="password" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-2">Deixe em branco para manter a senha atual.</p>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Confirmar senha</label>
                    <input type="password" name="password_confirmation" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- FOTOS (FILEPOND) --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-image text-slate-500"></i> Fotos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <!-- Foto de Perfil -->
                <div>
                    <label class="text-sm font-bold text-slate-700 mb-2 block">Foto de perfil</label>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-20 h-20 rounded-full overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                            @if($user->photo)
                                <img src="{{ asset($user->photo) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-xl">
                                    {{ mb_substr((string) $user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" class="filepond" name="photo" data-allow-reorder="true"
                                data-max-file-size="5MB" data-max-files="1" accept="image/png, image/jpeg, image/gif">
                            <p class="text-xs text-slate-500 mt-1">Recomendado: 500x500px (JPG/PNG)</p>
                        </div>
                    </div>
                </div>

                <!-- Capa -->
                <div>
                    <label class="text-sm font-bold text-slate-700 mb-2 block">Capa do Perfil</label>
                    @if($user->cover_photo)
                        <div class="w-full h-24 rounded-lg overflow-hidden bg-slate-100 mb-3 border border-slate-200">
                            <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input type="file" class="filepond" name="cover_photo" data-allow-reorder="true"
                        data-max-file-size="5MB" data-max-files="1" accept="image/png, image/jpeg, image/gif">
                    <p class="text-xs text-slate-500 mt-1">Recomendado: 1200x300px (JPG/PNG)</p>
                </div>
            </div>
        </div>

        {{-- ENDEREÇO --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-slate-500"></i> Endereço
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">CEP</label>
                    <input name="cep" id="profile_cep" value="{{ old('cep', $user->cep) }}" maxlength="9"
                        inputmode="numeric" autocomplete="postal-code" data-mask-cep placeholder="00000-000"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Rua/Avenida</label>
                    <input name="street" id="profile_street" value="{{ old('street', $user->street) }}"
                        placeholder="Nome da rua ou avenida"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Número</label>
                    <input name="number" id="profile_number" value="{{ old('number', $user->number) }}" maxlength="10"
                        inputmode="text" placeholder="Nº"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Complemento</label>
                    <input name="complement" value="{{ old('complement', $user->complement) }}" maxlength="40"
                        placeholder="Apto, bloco, sala..."
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bairro</label>
                    <input name="neighborhood" id="profile_neighborhood"
                        value="{{ old('neighborhood', $user->neighborhood) }}" maxlength="60" placeholder="Bairro"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Cidade</label>
                    <input name="city" id="profile_city" value="{{ old('city', $user->city) }}" maxlength="60"
                        placeholder="Cidade"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">UF</label>
                    <input name="state" id="profile_state" value="{{ old('state', $user->state) }}" maxlength="2"
                        placeholder="UF"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- REDES SOCIAIS --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-share-alt text-slate-500"></i> Redes sociais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Website</label>
                    <input name="website" value="{{ old('website', $user->website) }}" maxlength="120"
                        placeholder="https://seusite.com.br"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Instagram</label>
                    <input name="instagram" value="{{ old('instagram', $user->instagram) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Facebook</label>
                    <input name="facebook" value="{{ old('facebook', $user->facebook) }}" maxlength="80"
                        placeholder="/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Twitter</label>
                    <input name="twitter" value="{{ old('twitter', $user->twitter) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">LinkedIn</label>
                    <input name="linkedin" value="{{ old('linkedin', $user->linkedin) }}" maxlength="80"
                        placeholder="/in/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">YouTube</label>
                    <input name="youtube" value="{{ old('youtube', $user->youtube) }}" maxlength="80"
                        placeholder="/c/seucanal"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- PRIVACIDADE --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-lock text-slate-500"></i> Privacidade
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="show_email_public" {{ old('show_email_public', (bool) $user->show_email_public) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-slate-700">Mostrar e-mail publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="show_phone_public" {{ old('show_phone_public', (bool) $user->show_phone_public) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-slate-700">Mostrar telefone publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="show_address_public" {{ old('show_address_public', (bool) $user->show_address_public) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-slate-700">Mostrar endereço publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="hide_profile" {{ old('hide_profile', (bool) $user->hide_profile) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-slate-700">Ocultar meu perfil nas buscas</span>
                </label>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-end pb-8">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-8 py-3 text-sm font-extrabold text-white hover:brightness-110 transition shadow-lg shadow-blue-500/30">
                <i class="fas fa-save mr-2"></i> Salvar alterações
            </button>
        </div>
    </form>

    @push('scripts')
        <style>
            /* Hide FilePond Credits */
            .filepond--credits {
                display: none !important;
                visibility: hidden;
                pointer-events: none;
            }

            .filepond--root {
                margin-bottom: 0;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // --- 1. FilePond Uploads ---
                FilePond.registerPlugin(
                    FilePondPluginImagePreview,
                    FilePondPluginFileValidateSize,
                    FilePondPluginFileValidateType
                );

                // Turn all file input elements into ponds
                const ponds = document.querySelectorAll('input.filepond');
                ponds.forEach(input => {
                    FilePond.create(input, {
                        labelIdle: '<span class="text-xs">Arraste ou <span class="filepond--label-action">clique</span> para alterar</span>',
                        credits: false, // Remove "Powered by PQINA"
                        storeAsFile: true,
                        labelFileProcessing: 'Enviando...',
                        labelFileProcessingComplete: 'Pronto',
                        labelTapToCancel: 'cancelar',
                        labelTapToUndo: 'desfazer',
                        stylePanelLayout: 'compact',
                        styleLoadIndicatorPosition: 'center',
                        styleProgressIndicatorPosition: 'right bottom',
                        styleButtonRemoveItemPosition: 'left bottom',
                        styleButtonProcessItemPosition: 'right bottom',
                    });
                });

                // --- 2. Máscaras Inputmask ---
                // Verifica se Inputmask carregou
                if (typeof Inputmask !== 'undefined') {
                    // Telefone
                    Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(document.querySelectorAll('[data-mask-phone]'));

                    // CPF/CNPJ
                    Inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true }).mask(document.querySelectorAll('[data-mask-doc]'));

                    // CEP
                    Inputmask('99999-999').mask(document.querySelectorAll('[data-mask-cep]'));
                } else {
                    console.error('Inputmask não foi carregado corretamente.');
                }

                // --- 4. Auto-Preenchimento de CEP ---
                const cepInput = document.getElementById('profile_cep');
                if (cepInput) {
                    cepInput.addEventListener('input', function (e) {
                        let cep = e.target.value.replace(/\D/g, '');
                        if (cep.length === 8) {
                            // Feedback visual (loading)
                            cepInput.parentElement.classList.add('opacity-50');

                            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                                .then(response => response.json())
                                .then(data => {
                                    if (!data.erro) {
                                        if (document.getElementById('profile_street')) document.getElementById('profile_street').value = data.logradouro;
                                        if (document.getElementById('profile_neighborhood')) document.getElementById('profile_neighborhood').value = data.bairro;
                                        if (document.getElementById('profile_city')) document.getElementById('profile_city').value = data.localidade;
                                        if (document.getElementById('profile_state')) document.getElementById('profile_state').value = data.uf;

                                        // Foca no número
                                        setTimeout(() => {
                                            const numInput = document.getElementById('profile_number');
                                            if (numInput) numInput.focus();
                                        }, 100);
                                    }
                                })
                                .catch(err => console.error('Erro ViaCEP:', err))
                                .finally(() => {
                                    cepInput.parentElement.classList.remove('opacity-50');
                                });
                        }
                    });
                }

                // --- 5. Lógica de Segmento e Interesses (Outros) ---
                const segmentSelect = document.getElementById('segment_select');
                const segmentCustom = document.getElementById('segment_custom');
                if (segmentSelect && segmentCustom) {
                    segmentSelect.addEventListener('change', function () {
                        if (this.value === 'Outros') {
                            segmentCustom.classList.remove('hidden');
                            segmentCustom.focus();
                        } else {
                            segmentCustom.classList.add('hidden');
                        }
                    });
                }

                const interestsCb = document.getElementById('interests_other_cb');
                const interestsCustom = document.getElementById('interests_custom');
                if (interestsCb && interestsCustom) {
                    interestsCb.addEventListener('change', function () {
                        if (this.checked) {
                            interestsCustom.classList.remove('hidden');
                            interestsCustom.focus();
                        } else {
                            interestsCustom.classList.add('hidden');
                            interestsCustom.value = ''; // Clear value if unchecked
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection