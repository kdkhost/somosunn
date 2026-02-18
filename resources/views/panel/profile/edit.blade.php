@extends('panel.layouts.app')

@section('title', 'Meu Perfil - UNN')

@section('panel_content')
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">Meu perfil</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Mantenha seus dados atualizados para melhor experiência
                    na plataforma.</p>
            </div>
            <a href="{{ route('panel.dashboard') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                data-tooltip="Voltar para o painel principal" style="position:relative;">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        {{-- DADOS PESSOAIS --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
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
                        <option value="F" {{ old('person_type', strlen($user->doc) > 11 ? 'J' : 'F') == 'F' ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="J" {{ old('person_type', strlen($user->doc) > 11 ? 'J' : 'F') == 'J' ? 'selected' : '' }}>Pessoa Jurídica</option>
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
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="120"
                        placeholder="exemplo@email.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm dark:bg-slate-950 dark:text-white focus:border-blue-500 focus:ring-blue-500">
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

        {{-- FOTOS (FILEPOND) --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-image text-slate-500"></i> Fotos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <!-- Foto de Perfil -->
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Foto de perfil</label>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-20 h-20 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex-shrink-0 border border-slate-200 dark:border-slate-700">
                            @if($user->photo)
                                <img src="{{ asset($user->photo) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 font-bold text-xl">
                                    {{ mb_substr((string) $user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" class="filepond" name="photo" data-allow-reorder="true"
                                data-max-file-size="5MB" data-max-files="1" accept="image/png, image/jpeg, image/gif">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Recomendado: 500x500px (JPG/PNG)</p>
                        </div>
                    </div>
                </div>

                <!-- Capa -->
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Capa do Perfil</label>
                    @if($user->cover_photo)
                        <div
                            class="w-full h-24 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 mb-3 border border-slate-200 dark:border-slate-700">
                            <img src="{{ asset($user->cover_photo) }}" alt="Capa" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input type="file" class="filepond" name="cover_photo" data-allow-reorder="true"
                        data-max-file-size="5MB" data-max-files="1" accept="image/png, image/jpeg, image/gif">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Recomendado: 1200x300px (JPG/PNG)</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
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
                        if(docLabel) docLabel.textContent = label;
                    }

                    if (personTypeSelect) {
                        personTypeSelect.addEventListener('change', function() {
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