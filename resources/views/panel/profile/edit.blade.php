@extends('panel.layouts.app')

@section('title', 'Meu Perfil - UNN')

@section('panel_content')
    @php
        $isMarketingManager = (int) \App\Models\Setting::get('platform_marketing_user_id', 0) === (int) $user->id;
    @endphp

    @if($isMarketingManager)
        <div class="mb-6 rounded-2xl border border-purple-200 dark:border-purple-800 bg-gradient-to-r from-purple-50 to-fuchsia-50 dark:from-purple-900/20 dark:to-fuchsia-900/20 p-4 shadow-sm">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-sm text-purple-900 dark:text-purple-100">Responsavel de Marketing</h3>
                    <p class="text-xs text-purple-700 dark:text-purple-300">Acompanhe os valores na area exclusiva.</p>
                </div>
                <a href="{{ route('panel.marketing.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 px-3 py-1.5 text-xs font-bold text-white transition">
                    <i class="fas fa-arrow-right text-[10px]"></i> Acessar
                </a>
            </div>
        </div>
    @endif

    {{-- HERO CARD: Capa + Avatar + Nome --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
        {{-- Capa --}}
        <div id="cover-preview"
            class="relative w-full h-32 md:h-40 bg-gradient-to-r from-blue-500 to-indigo-600 cursor-pointer group"
            onclick="document.getElementById('cover-input').click()">
            @if($user->cover_photo)
                <img src="{{ asset($user->cover_photo) }}?t={{ time() }}" alt="Capa" class="w-full h-full object-cover" id="cover-img">
            @else
                <div class="w-full h-full flex items-center justify-center" id="cover-img">
                    <span class="text-white/60 text-sm font-bold"><i class="fas fa-camera mr-2"></i>Clique para adicionar capa</span>
                </div>
            @endif
            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-white text-sm font-bold flex items-center gap-2"><i class="fas fa-camera"></i> Alterar capa (1200x300)</span>
            </div>
            <input type="file" id="cover-input" class="hidden" accept="image/png,image/jpeg,image/gif,image/webp" data-crop-type="cover_photo" data-crop-ratio="{{ 1200/300 }}">
            <div id="cover-status" class="hidden absolute bottom-2 right-2 text-xs font-bold text-white bg-emerald-500 px-2 py-1 rounded-lg">
                <i class="fas fa-check-circle"></i> Capa atualizada!
            </div>
        </div>

        {{-- Avatar + Info --}}
        <div class="px-6 pb-5 -mt-10 relative z-10">
            <div class="flex items-end gap-4">
                <div id="avatar-preview"
                    class="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden bg-white dark:bg-slate-800 border-4 border-white dark:border-slate-900 shadow-lg cursor-pointer relative group flex-shrink-0"
                    onclick="document.getElementById('avatar-input').click()">
                    @if($user->photo)
                        <img src="{{ asset($user->photo) }}?t={{ time() }}" alt="Avatar" class="w-full h-full object-cover" id="avatar-img">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 font-black text-2xl bg-slate-100 dark:bg-slate-800" id="avatar-img">
                            {{ mb_substr((string) $user->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl">
                        <i class="fas fa-camera text-white"></i>
                    </div>
                </div>
                <input type="file" id="avatar-input" class="hidden" accept="image/png,image/jpeg,image/gif,image/webp" data-crop-type="photo" data-crop-ratio="1">

                <div class="flex-1 min-w-0 pb-1">
                    <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white truncate">{{ $user->name }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    <div id="avatar-status" class="hidden mt-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-check-circle"></i> Foto atualizada!
                    </div>
                </div>

                <a href="{{ route('panel.dashboard') }}"
                    class="hidden md:inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="fas fa-arrow-left text-xs"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- Modal de Crop --}}
    <div id="crop-modal" class="hidden fixed inset-0 z-[9999] bg-black/70 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-black text-slate-900 dark:text-white">Recortar imagem</h3>
                <button type="button" onclick="closeCropModal()" class="text-slate-400 hover:text-slate-600 text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-4">
                <div id="crop-container" class="w-full" style="max-height: 400px;">
                    <img id="crop-image" src="" alt="Crop" class="max-w-full">
                </div>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                <button type="button" onclick="closeCropModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Cancelar
                </button>
                <button type="button" onclick="applyCrop()" id="crop-apply-btn"
                    class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-black transition-colors flex items-center gap-2">
                    <i class="fas fa-crop-alt"></i> Recortar e salvar
                </button>
            </div>
        </div>
    </div>

    @if(request()->routeIs('curriculum.register') || request()->has('ref_curriculum'))
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4 rounded-2xl flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-blue-900 dark:text-blue-100 italic">Banco de Talentos</h4>
                <p class="text-xs text-blue-700 dark:text-blue-300">Complete seu perfil abaixo para que empresas parceiras possam te encontrar!</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('panel.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        {{-- DADOS PESSOAIS --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Nome completo *</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required maxlength="80"
                        placeholder="Digite seu nome completo"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="person_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipo
                        de Pessoa</label>
                    <select name="person_type" id="person_type"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-sm">
                        <option value="F" {{ old('person_type', strlen(preg_replace('/\D/', '', $user->doc)) > 11 ? 'J' : 'F') == 'F' ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="J" {{ old('person_type', strlen(preg_replace('/\D/', '', $user->doc)) > 11 ? 'J' : 'F') == 'J' ? 'selected' : '' }}>Pessoa Jurídica</option>
                    </select>
                </div>

                <div>
                    <label for="gender"
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gênero</label>
                    <select name="gender" id="gender"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-sm">
                        <option value="">Selecione...</option>
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Masculino
                        </option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Feminino
                        </option>
                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Outro</option>
                        <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Prefiro não dizer</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Data de nascimento</label>
                    <input type="date" name="birth_date"
                        value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Usado para o bônus de aniversário.</p>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>E-mail *</span>
                        @if($user->hasVerifiedEmail())
                            <span class="text-[10px] bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Verificado
                            </span>
                        @else
                            <span class="text-[10px] bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i> Pendente
                            </span>
                        @endif
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="120"
                        placeholder="exemplo@email.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    
                    @if(!$user->hasVerifiedEmail())
                        <div class="mt-2 flex items-center justify-between bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20 p-3 rounded-xl border-dashed">
                            <span class="text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Não recebeu o e-mail?
                            </span>
                            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:text-amber-700 underline decoration-dashed">
                                    Reenviar agora
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Telefone</label>
                    <input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" inputmode="tel"
                        autocomplete="tel" data-mask-phone placeholder="(99) 99999-9999"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300" id="doc_label">Documento
                        (CPF)</label>
                    <input name="doc" id="profile_doc" value="{{ old('doc', $user->doc) }}" maxlength="18"
                        inputmode="numeric" autocomplete="off" data-mask-doc placeholder="000.000.000-00"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Ocupação</label>
                    <input name="occupation" value="{{ old('occupation', $user->occupation) }}" maxlength="60"
                        placeholder="Sua profissão"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Empresa</label>
                    <input name="company" value="{{ old('company', $user->company) }}" maxlength="60"
                        placeholder="Nome da empresa"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Segmento</label>
                    <select name="segment_select" id="segment_select"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-950 dark:text-white">
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
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500 {{ !in_array(old('segment', $user->segment), ['Tecnologia', 'Marketing', 'Vendas', 'Saúde', 'Educação', 'Direito', 'Engenharia', 'Finanças', 'Imobiliário', 'Alimentação', 'Consultoria', 'RH', '', null]) ? '' : 'hidden' }}">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Interesses</label>
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
                                        class="rounded text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-700">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $interest }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 cursor-pointer col-span-2">
                                <input type="checkbox" id="interests_other_cb" {{ $hasCustomInterest ? 'checked' : '' }}
                                    class="rounded text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-700">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Outros</span>
                            </label>
                        </div>
                        <input name="interests_custom" id="interests_custom"
                            value="{{ $hasCustomInterest ? implode(', ', array_diff($userInterests, $definedInterests)) : '' }}"
                            placeholder="Quais outros interesses? (Separe por vírgula)"
                            class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500 {{ $hasCustomInterest ? '' : 'hidden' }}">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Bio</label>
                    <textarea name="bio" rows="4" maxlength="500" placeholder="Conte um pouco sobre você"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">{{ old('bio', $user->bio) }}</textarea>
                </div>
                @if($user->canManageReceivingPixKey())
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                        <i class="fas fa-wallet"></i> Chave PIX para Recebimentos
                    </label>
                    <input name="pix_key" value="{{ old('pix_key', $user->pix_key) }}" maxlength="255"
                        placeholder="E-mail, CPF, Celular ou Chave Aleatória"
                        class="mt-2 w-full rounded-2xl border border-blue-100 dark:border-blue-900/30 bg-blue-50/20 dark:bg-blue-900/10 px-4 py-3 text-sm dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Esta chave será utilizada nos splits destinados ao seu perfil.</p>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Nova senha</label>
                    <input type="password" name="password" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Deixe em branco para manter a senha atual.
                    </p>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Confirmar senha</label>
                    <input type="password" name="password_confirmation" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- ENDEREÇO --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-slate-500"></i> Endereço
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">CEP</label>
                    <input name="cep" id="profile_cep" value="{{ old('cep', $user->cep) }}" maxlength="9"
                        inputmode="numeric" autocomplete="postal-code" data-mask-cep placeholder="00000-000"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Rua/Avenida</label>
                    <input name="street" id="profile_street" value="{{ old('street', $user->street) }}"
                        placeholder="Nome da rua ou avenida"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Número</label>
                    <input name="number" id="profile_number" value="{{ old('number', $user->number) }}" maxlength="10"
                        inputmode="text" placeholder="Nº"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Complemento</label>
                    <input name="complement" value="{{ old('complement', $user->complement) }}" maxlength="40"
                        placeholder="Apto, bloco, sala..."
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Bairro</label>
                    <input name="neighborhood" id="profile_neighborhood"
                        value="{{ old('neighborhood', $user->neighborhood) }}" maxlength="60" placeholder="Bairro"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Cidade</label>
                    <input name="city" id="profile_city" value="{{ old('city', $user->city) }}" maxlength="60"
                        placeholder="Cidade"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">UF</label>
                    <input name="state" id="profile_state" value="{{ old('state', $user->state) }}" maxlength="2"
                        placeholder="UF"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm uppercase dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- REDES SOCIAIS --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-share-alt text-slate-500"></i> Redes sociais
            </h2>
            <div class="grid grid-cols-1 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Website</label>
                    <input name="website" value="{{ old('website', $user->website) }}" maxlength="120"
                        placeholder="https://seusite.com.br"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Instagram</label>
                    <input name="instagram" value="{{ old('instagram', $user->instagram) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Facebook</label>
                    <input name="facebook" value="{{ old('facebook', $user->facebook) }}" maxlength="80"
                        placeholder="/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Twitter</label>
                    <input name="twitter" value="{{ old('twitter', $user->twitter) }}" maxlength="80"
                        placeholder="@seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">LinkedIn</label>
                    <input name="linkedin" value="{{ old('linkedin', $user->linkedin) }}" maxlength="80"
                        placeholder="/in/seuusuario"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">YouTube</label>
                    <input name="youtube" value="{{ old('youtube', $user->youtube) }}" maxlength="80"
                        placeholder="/c/seucanal"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- PRIVACIDADE E PREFERÊNCIAS --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-lock text-slate-500"></i> Privacidade e Preferências
            </h2>
            <div class="grid grid-cols-1 gap-4 mt-5">
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <input type="checkbox" name="show_email_public" {{ old('show_email_public', (bool) $user->show_email_public) ? 'checked' : '' }}
                        class="rounded text-blue-600 focus:ring-blue-500 dark:bg-slate-950">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Mostrar e-mail
                        publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <input type="checkbox" name="show_phone_public" {{ old('show_phone_public', (bool) $user->show_phone_public) ? 'checked' : '' }}
                        class="rounded text-blue-600 focus:ring-blue-500 dark:bg-slate-950">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Mostrar telefone
                        publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <input type="checkbox" name="show_address_public" {{ old('show_address_public', (bool) $user->show_address_public) ? 'checked' : '' }}
                        class="rounded text-blue-600 focus:ring-blue-500 dark:bg-slate-950">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Mostrar endereço
                        publicamente</span>
                </label>
                <label
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <input type="checkbox" name="hide_profile" {{ old('hide_profile', (bool) $user->hide_profile) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 dark:bg-slate-950">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Ocultar meu perfil nas
                        buscas</span>
                </label>

                <div class="md:col-span-2 mt-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Tema da
                        Plataforma</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label
                            class="relative flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition has-[:checked]:border-blue-600 dark:has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 dark:has-[:checked]:bg-blue-900/10 group">
                            <input type="radio" name="theme_pref" value="light" {{ old('theme_pref', $user->theme_pref ?? 'light') == 'light' ? 'checked' : '' }} class="hidden">
                            <div
                                class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 dark:text-white">Modo Claro</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Interface clássica e brilhante</div>
                            </div>
                            <div class="ml-auto opacity-0 group-has-[:checked]:opacity-100 transition-opacity">
                                <i class="fas fa-check-circle text-blue-600 dark:text-blue-500"></i>
                            </div>
                        </label>
                        <label
                            class="relative flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition has-[:checked]:border-blue-600 dark:has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 dark:has-[:checked]:bg-blue-900/10 group">
                            <input type="radio" name="theme_pref" value="dark" {{ old('theme_pref', $user->theme_pref ?? 'light') == 'dark' ? 'checked' : '' }} class="hidden">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                                <i class="fas fa-moon"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 dark:text-white">Modo Escuro</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Conforto visual em ambientes escuros
                                </div>
                            </div>
                            <div class="ml-auto opacity-0 group-has-[:checked]:opacity-100 transition-opacity">
                                <i class="fas fa-check-circle text-blue-600 dark:text-blue-500"></i>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-end pb-8">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-8 py-3 text-sm font-extrabold text-white hover:brightness-110 transition shadow-lg shadow-blue-500/30">
                <i class="fas fa-save mr-2"></i> Salvar alterações
            </button>
        </div>
    </form>

    {{-- LINK DE INDICAÇÃO --}}
    @php
        $referralLink = route('register') . '?ref=' . ($user->referral_code ?? '');
    @endphp
    <div class="mt-4 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-user-plus text-slate-500"></i> Indique e ganhe
        </h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Compartilhe seu link exclusivo. Quando alguém se cadastrar usando ele, você ganha
            <strong>100 pontos</strong>!
        </p>
        <div class="mt-4 flex items-center gap-2">
            <input id="referral_link_input" type="text" readonly value="{{ $referralLink }}"
                class="flex-1 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-slate-50 dark:bg-slate-950 dark:text-white">
            <button onclick="copyReferralLink(event)" type="button"
                class="inline-flex items-center gap-2 rounded-full bg-[#1F5EDB] px-5 py-3 text-sm font-bold text-white hover:brightness-110 transition">
                <i class="fas fa-copy"></i> Copiar
            </button>
        </div>
        @if($user->referral_code)
            <div class="mt-2 flex gap-3 flex-wrap">
                <a href="https://wa.me/?text={{ urlencode('Ei! Entre na plataforma usando meu link e comece aprendendo: ' . $referralLink) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-xs text-green-600 hover:underline font-bold">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite!') }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-xs text-blue-500 hover:underline font-bold">
                    <i class="fab fa-telegram"></i> Telegram
                </a>
            </div>
        @endif
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
        <script>
        // ── Crop & Auto-Upload para fotos de perfil ──
        (function() {
            var cropper = null;
            var currentType = '';
            var currentRatio = 1;
            var uploadUrl = @json(route('panel.profile.upload-photo'));
            var csrfToken = @json(csrf_token());

            // Abrir modal de crop ao selecionar arquivo
            document.getElementById('avatar-input').addEventListener('change', function(e) {
                openCropModal(e.target, 'photo', 1);
            });
            document.getElementById('cover-input').addEventListener('change', function(e) {
                openCropModal(e.target, 'cover_photo', 1200/300);
            });

            window.openCropModal = function(input, type, ratio) {
                if (!input.files || !input.files[0]) return;
                currentType = type;
                currentRatio = ratio;

                var reader = new FileReader();
                reader.onload = function(ev) {
                    var img = document.getElementById('crop-image');
                    img.src = ev.target.result;
                    document.getElementById('crop-modal').classList.remove('hidden');

                    // Destruir cropper anterior
                    if (cropper) { cropper.destroy(); cropper = null; }

                    setTimeout(function() {
                        cropper = new Cropper(img, {
                            aspectRatio: ratio,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            responsive: true,
                            background: false,
                        });
                    }, 100);
                };
                reader.readAsDataURL(input.files[0]);
            };

            window.closeCropModal = function() {
                document.getElementById('crop-modal').classList.add('hidden');
                if (cropper) { cropper.destroy(); cropper = null; }
                // Reset inputs
                document.getElementById('avatar-input').value = '';
                document.getElementById('cover-input').value = '';
            };

            window.applyCrop = function() {
                if (!cropper) return;

                var btn = document.getElementById('crop-apply-btn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

                var canvas = cropper.getCroppedCanvas({
                    width: currentType === 'photo' ? 500 : 1200,
                    height: currentType === 'photo' ? 500 : 300,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                canvas.toBlob(function(blob) {
                    var formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('type', currentType);
                    formData.append('image', blob, 'cropped.jpg');

                    fetch(uploadUrl, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-crop-alt"></i> Recortar e salvar';

                        if (data.success) {
                            closeCropModal();

                            // Atualizar preview
                            if (currentType === 'photo') {
                                var preview = document.getElementById('avatar-preview');
                                preview.innerHTML = '<img src="' + data.url + '" alt="Avatar" class="w-full h-full object-cover" id="avatar-img"><div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full"><i class="fas fa-camera text-white text-lg"></i></div>';
                                document.getElementById('avatar-status').classList.remove('hidden');
                                setTimeout(function() { document.getElementById('avatar-status').classList.add('hidden'); }, 4000);
                            } else {
                                var preview = document.getElementById('cover-preview');
                                preview.innerHTML = '<img src="' + data.url + '" alt="Capa" class="w-full h-full object-cover" id="cover-img"><div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><i class="fas fa-camera text-white text-lg"></i></div>';
                                document.getElementById('cover-status').classList.remove('hidden');
                                setTimeout(function() { document.getElementById('cover-status').classList.add('hidden'); }, 4000);
                            }

                            if (typeof toastr !== 'undefined') toastr.success(data.message || 'Imagem atualizada!');
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error(data.error || 'Erro ao salvar imagem.');
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-crop-alt"></i> Recortar e salvar';
                        if (typeof toastr !== 'undefined') toastr.error('Erro de conexao. Tente novamente.');
                    });
                }, 'image/jpeg', 0.9);
            };
        })();
        </script>

        <script>
        if (typeof referralProfileTrackUrl === 'undefined') {
        var referralProfileTrackUrl = @json(route('panel.referral.track'));
        var referralProfileTrackToken = @json(csrf_token());

        function trackReferralProfileAction(action, channel, targetUrl) {
            const payload = new FormData();
            payload.append('_token', referralProfileTrackToken);
            payload.append('action', action);
            payload.append('channel', channel);
            payload.append('context', 'panel_profile');
            if (targetUrl) {
                payload.append('target_url', targetUrl);
            }

            if (navigator.sendBeacon) {
                navigator.sendBeacon(referralProfileTrackUrl, payload);
                return;
            }

            fetch(referralProfileTrackUrl, {
                method: 'POST',
                body: payload,
                credentials: 'same-origin',
                keepalive: true,
            }).catch(() => {});
        }

        function copyReferralLink(event) {
            var input = document.getElementById('referral_link_input');
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(input.value);
            } catch(e) {
                document.execCommand('copy');
            }
            trackReferralProfileAction('copy', 'copy', input.value);
            var btn = event.currentTarget;
            var original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
            setTimeout(function(){ btn.innerHTML = original; }, 2000);
        }

        var referralCard = document.getElementById('referral_link_input');
        if (referralCard) {
            referralCard = referralCard.closest('div.mt-4');
        }

        if (referralCard) {
            referralCard.querySelectorAll('a[href^="https://wa.me/"], a[href^="https://t.me/share/"]').forEach(function (link) {
                link.addEventListener('click', function () {
                    var channel = link.href.indexOf('wa.me') !== -1 ? 'whatsapp' : 'telegram';
                    trackReferralProfileAction('share', channel, link.href);
                });
            });
        }
        } // end if typeof referralProfileTrackUrl
        </script>
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
                if (typeof window.initializePanelFileUploads === 'function') {
                    window.initializePanelFileUploads(document);
                }

                // --- 2. Máscaras Inputmask ---
                // Verifica se Inputmask carregou
                if (typeof Inputmask !== 'undefined') {
                    // Telefone
                    Inputmask('(99) 9999[9]-9999').mask(document.querySelectorAll('[data-mask-phone]'));

                    // CPF/CNPJ Mask Logic
                    const docInput = document.getElementById('profile_doc');
                    const personTypeSelect = document.getElementById('person_type');
                    const docLabel = document.getElementById('doc_label');

                    function updateDocMask() {
                        if (!docInput || !personTypeSelect) return;

                        const isJuridica = personTypeSelect.value === 'J';
                        const mask = isJuridica ? '99.999.999/9999-99' : '999.999.999-99';
                        const placeholder = isJuridica ? '00.000.000/0000-00' : '000.000.000-00';
                        const label = isJuridica ? 'Documento (CNPJ)' : 'Documento (CPF)';

                        if (docInput.inputmask) docInput.inputmask.remove();
                        Inputmask({ mask: mask, keepStatic: true }).mask(docInput);

                        docInput.placeholder = placeholder;
                        if (docLabel) docLabel.textContent = label;
                    }

                    if (personTypeSelect) {
                        personTypeSelect.addEventListener('change', function () {
                            docInput.value = ''; // Clear on change to avoid mask conflict
                            updateDocMask();
                        });
                        // Init
                        updateDocMask();
                    } else {
                        // Fallback if no select
                        Inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true }).mask(docInput);
                    }

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

                // --- 4.1 Auto-Preenchimento de CNPJ ---
                const docInput = document.querySelector('[name="doc"]');
                const companyInput = document.querySelector('[name="company"]');

                if (docInput && companyInput) {
                    docInput.addEventListener('input', function (e) {
                        let doc = e.target.value.replace(/\D/g, '');

                        // CNPJ tem 14 dígitos
                        if (doc.length === 14) {
                            companyInput.parentElement.classList.add('opacity-50');

                            fetch(`https://brasilapi.com.br/api/cnpj/v1/${doc}`)
                                .then(response => {
                                    if (!response.ok) throw new Error('CNPJ não encontrado');
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.razao_social) {
                                        companyInput.value = data.razao_social;
                                        // Também preenche o nome fantasia se disponível e o campo empresa estiver vazio ou igual à razão social
                                        if (data.nome_fantasia && (companyInput.value === '' || companyInput.value === data.razao_social)) {
                                            companyInput.value = data.nome_fantasia; // Preferencia por nome fantasia ou manter razao social? O usuario pediu "nome da empresa". Geralmente Razão Social é mais formal. Vou usar Razão Social como principal mas se tiver fantasia talvez seja melhor. O usuario disse "nome da empresa devera ser preenchido automaticamente de acordo com o resistrado na receita federal". Receita usa Razão Social.
                                            // Vou manter Razão Social primeiro, mas se quiser fantasia podemos ajustar.
                                            // Ajuste: O usuário pediu "nome da empresa... de acordo com o registrado na receita federal". Isso soa como Razão Social.
                                            companyInput.value = data.razao_social;
                                        }

                                        // Opcional: Preencher endereço se disponível e campos estiverem vazios
                                        if (data.cep && (!document.getElementById('profile_cep').value)) {
                                            document.getElementById('profile_cep').value = data.cep;
                                            // Disparar evento de input no CEP para buscar o endereço completo via ViaCEP (ou usar os dados da BrasilAPI)
                                            document.getElementById('profile_cep').dispatchEvent(new Event('input'));
                                        }
                                    }
                                })
                                .catch(err => console.error('Erro BrasilAPI:', err))
                                .finally(() => {
                                    companyInput.parentElement.classList.remove('opacity-50');
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
