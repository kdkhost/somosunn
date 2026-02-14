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

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nome completo *</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required maxlength="80"
                        placeholder="Digite seu nome completo"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="120"
                        placeholder="exemplo@email.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Telefone</label>
                    <input name="phone" value="{{ old('phone', $user->phone) }}"
                        maxlength="20" inputmode="tel" autocomplete="tel" data-mask-phone
                        placeholder="(99) 99999-9999"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Documento</label>
                    <input name="doc" value="{{ old('doc', $user->doc) }}"
                        maxlength="18" inputmode="numeric" autocomplete="off" data-mask-doc
                        placeholder="CPF ou CNPJ"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Ocupação</label>
                    <input name="occupation" value="{{ old('occupation', $user->occupation) }}" maxlength="60"
                        placeholder="Sua profissão"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Empresa</label>
                    <input name="company" value="{{ old('company', $user->company) }}" maxlength="60"
                        placeholder="Nome da empresa"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Segmento</label>
                    <input name="segment" value="{{ old('segment', $user->segment) }}" maxlength="60"
                        placeholder="Área de atuação"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Interesses</label>
                    <input name="interests" value="{{ old('interests', $user->interests) }}" maxlength="120"
                        placeholder="Ex: tecnologia, marketing, saúde..."
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bio</label>
                    <textarea name="bio" rows="4" maxlength="500"
                        placeholder="Conte um pouco sobre você"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nova senha</label>
                    <input type="password" name="password" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p class="text-xs text-slate-500 mt-2">Deixe em branco para manter a senha atual.</p>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Confirmar senha</label>
                    <input type="password" name="password_confirmation" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
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
                            @if($user->photo)
                                <img id="profile-photo-preview" src="{{ asset($user->photo) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <span class="text-slate-500 font-bold">{{ mb_substr((string) $user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div id="profile-photo-drop" class="relative w-full">
                            <input type="file" name="photo" id="profile-photo-input" accept="image/*"
                                class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#1F5EDB] file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:brightness-110">
                            <div id="profile-photo-dropzone" class="mt-2 flex items-center justify-center border-2 border-dashed border-slate-300 rounded-xl py-4 text-slate-400 cursor-pointer transition hover:border-blue-400">
                                <span class="text-sm">Arraste e solte uma imagem aqui ou clique para selecionar</span>
                            </div>
                        </div>
                    </div>
                @push('scripts')
                <script>
                // Drag & drop foto de perfil
                function initProfilePhotoDrop() {
                    var dropzone = document.getElementById('profile-photo-dropzone');
                    var input = document.getElementById('profile-photo-input');
                    var preview = document.getElementById('profile-photo-preview');
                    if (!dropzone || !input) return;
                    dropzone.addEventListener('click', function() { input.click(); });
                    dropzone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        dropzone.classList.add('border-blue-400', 'bg-blue-50');
                    });
                    dropzone.addEventListener('dragleave', function(e) {
                        e.preventDefault();
                        dropzone.classList.remove('border-blue-400', 'bg-blue-50');
                    });
                    dropzone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        dropzone.classList.remove('border-blue-400', 'bg-blue-50');
                        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                            input.files = e.dataTransfer.files;
                            // Preview instantâneo
                            if (preview) {
                                var reader = new FileReader();
                                reader.onload = function(ev) {
                                    preview.src = ev.target.result;
                                };
                                reader.readAsDataURL(e.dataTransfer.files[0]);
                            }
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', function () {
                    initProfilePhotoDrop();
                });
                </script>
                @endpush
                </div>

                <div>
                    <div class="text-sm font-bold text-slate-700">Capa</div>
                    <div class="mt-3">
                        @if($user->cover_photo)
                            <div class="w-full h-28 rounded-2xl overflow-hidden bg-slate-100">
                                <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <input type="file" name="cover_photo" accept="image/*"
                            class="mt-3 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800">
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
                    <input name="cep" id="profile_cep" value="{{ old('cep', $user->cep) }}"
                        maxlength="9" inputmode="numeric" autocomplete="postal-code" data-mask-cep
                        placeholder="00000-000"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Rua/Avenida</label>
                    <input name="street" value="{{ old('street', $user->street) }}"
                        placeholder="Nome da rua ou avenida"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Número</label>
                    <input name="number" id="profile_number" value="{{ old('number', $user->number) }}"
                        maxlength="10" inputmode="numeric"
                        placeholder="Nº"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                @push('scripts')
                <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
                <script>
                // Máscaras
                function initMasks() {
                    if (window.Inputmask) {
                        var phone = document.querySelector('[data-mask-phone]');
                        if (phone) {
                            Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(phone);
                        }
                        var doc = document.querySelector('[data-mask-doc]');
                        if (doc) {
                            Inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true }).mask(doc);
                        }
                        var cep = document.querySelector('[data-mask-cep]');
                        if (cep) {
                            Inputmask('99999-999').mask(cep);
                        }
                    }
                }
                // Auto-avançar do CEP para número
                function initCepAutoAdvance() {
                    var cepInput = document.getElementById('profile_cep');
                    var numberInput = document.getElementById('profile_number');
                    if (!cepInput || !numberInput) return;
                    cepInput.addEventListener('input', function () {
                        var digits = (cepInput.value || '').replace(/\D/g, '');
                        if (digits.length === 8) {
                            setTimeout(function() { numberInput.focus(); }, 100);
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', function () {
                    initMasks();
                    initCepAutoAdvance();
                });
                </script>
                @endpush
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Complemento</label>
                    <input name="complement" value="{{ old('complement', $user->complement) }}" maxlength="40"
                        placeholder="Apto, bloco, sala..."
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bairro</label>
                    <input name="neighborhood" value="{{ old('neighborhood', $user->neighborhood) }}" maxlength="60"
                        placeholder="Bairro"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Cidade</label>
                    <input name="city" value="{{ old('city', $user->city) }}" maxlength="60"
                        placeholder="Cidade"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">UF</label>
                    <input name="state" value="{{ old('state', $user->state) }}" maxlength="2"
                        placeholder="UF"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase">
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
                    <input name="website" value="{{ old('website', $user->website) }}" maxlength="120"
                        placeholder="https://seusite.com.br"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Instagram</label>
                    <input name="instagram" value="{{ old('instagram', $user->instagram) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Facebook</label>
                    <input name="facebook" value="{{ old('facebook', $user->facebook) }}" maxlength="80"
                        placeholder="/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Twitter</label>
                    <input name="twitter" value="{{ old('twitter', $user->twitter) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">LinkedIn</label>
                    <input name="linkedin" value="{{ old('linkedin', $user->linkedin) }}" maxlength="80"
                        placeholder="/in/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">YouTube</label>
                    <input name="youtube" value="{{ old('youtube', $user->youtube) }}" maxlength="80"
                        placeholder="/c/seucanal"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
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
                class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-8 py-3 text-sm font-extrabold text-white hover:brightness-110 transition"
                data-tooltip="Salvar todas as alterações do perfil" style="position:relative;">
                <i class="fas fa-save mr-2"></i> Salvar alterações
            </button>
        @push('scripts')
        <script>
        // Tooltips simples (usando a cor do tema)
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-tooltip]').forEach(function(el) {
                el.addEventListener('mouseenter', function() {
                    let tip = document.createElement('div');
                    tip.className = 'custom-tooltip';
                    tip.innerText = el.getAttribute('data-tooltip');
                    tip.style.position = 'absolute';
                    tip.style.background = '#1F5EDB';
                    tip.style.color = '#fff';
                    tip.style.padding = '6px 12px';
                    tip.style.borderRadius = '8px';
                    tip.style.fontSize = '13px';
                    tip.style.zIndex = 1000;
                    tip.style.top = (el.offsetTop - 38) + 'px';
                    tip.style.left = (el.offsetLeft + el.offsetWidth/2 - 60) + 'px';
                    tip.style.boxShadow = '0 2px 8px 0 rgba(31,94,219,0.15)';
                    tip.classList.add('fade-in');
                    el.parentNode.appendChild(tip);
                    el._tip = tip;
                });
                el.addEventListener('mouseleave', function() {
                    if (el._tip) {
                        el._tip.remove();
                        el._tip = null;
                    }
                });
            });
        });
        </script>
        <style>
        .custom-tooltip {
            pointer-events: none;
            transition: opacity 0.15s;
            opacity: 0.98;
        }
        .custom-tooltip.fade-in {
            opacity: 1;
        }
        </style>
        @endpush
        </div>
    </form>
@endsection

