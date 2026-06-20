@php
    $inputClass = 'w-full px-4 py-3 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white';
    $labelClass = 'block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2';
    $documentDigits = preg_replace('/\D/', '', (string) old('doc', $user->doc));
    $personType = old('person_type', strlen($documentDigits) > 11 ? 'J' : 'F');
    $selectedRole = old('role', $user->role ?? 'member');
    $isMarketingManager = $user->exists && $user->isMarketingManager();
    $showPixKey = in_array($selectedRole, ['admin', 'superadmin'], true) || $isMarketingManager;
@endphp

<section class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-5">
        <i class="fas fa-id-card text-blue-600"></i>
        <h3 class="font-bold text-xl text-slate-800 dark:text-white">Dados profissionais</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="{{ $labelClass }}">Tipo de pessoa</label>
            <select name="person_type" id="panel-admin-user-person-type" class="{{ $inputClass }}">
                <option value="F" {{ $personType === 'F' ? 'selected' : '' }}>Pessoa Física</option>
                <option value="J" {{ $personType === 'J' ? 'selected' : '' }}>Pessoa Jurídica</option>
            </select>
        </div>
        <div>
            <label class="{{ $labelClass }}">CPF/CNPJ</label>
            <input name="doc" id="panel-admin-user-doc" inputmode="numeric" class="{{ $inputClass }}" value="{{ old('doc', $user->doc) }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Telefone</label>
            <input name="phone" id="panel-admin-user-phone" inputmode="tel" class="{{ $inputClass }}" value="{{ old('phone', $user->phone) }}" placeholder="(00) 00000-0000">
        </div>
        <div>
            <label class="{{ $labelClass }}">Data de nascimento</label>
            <input type="date" name="birth_date" class="{{ $inputClass }}" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Gênero</label>
            <select name="gender" class="{{ $inputClass }}">
                <option value="">Não informado</option>
                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Masculino</option>
                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Feminino</option>
                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Outro</option>
                <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Prefere não informar</option>
            </select>
        </div>
        <div>
            <label class="{{ $labelClass }}">Ocupação</label>
            <input name="occupation" class="{{ $inputClass }}" value="{{ old('occupation', $user->occupation) }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Empresa</label>
            <input name="company" class="{{ $inputClass }}" value="{{ old('company', $user->company) }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Segmento</label>
            <input name="segment" class="{{ $inputClass }}" value="{{ old('segment', $user->segment) }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">Interesses</label>
            <input name="interests" class="{{ $inputClass }}" value="{{ old('interests', $user->interests) }}" placeholder="Networking, cursos, eventos">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">Biografia</label>
            <textarea name="bio" rows="3" maxlength="500" class="{{ $inputClass }}">{{ old('bio', $user->bio) }}</textarea>
        </div>
        <div class="md:col-span-2 {{ $showPixKey ? '' : 'hidden' }}" id="panel-admin-user-pix-container"
            data-marketing-manager="{{ $isMarketingManager ? '1' : '0' }}"
            data-current-role="{{ $selectedRole }}">
            <label class="{{ $labelClass }}">Chave PIX para recebimentos</label>
            <input name="pix_key" id="panel-admin-user-pix-key" class="{{ $inputClass }}" value="{{ old('pix_key', $user->pix_key) }}"
                {{ $showPixKey ? 'required' : 'disabled' }}>
            <small class="text-xs text-slate-400">Obrigatória para Super Admin, Administrador e responsável de marketing.</small>
        </div>
    </div>
</section>

<section class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-5">
        <i class="fas fa-map-marker-alt text-blue-600"></i>
        <h3 class="font-bold text-xl text-slate-800 dark:text-white">Endereço</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-5">
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">CEP</label>
            <input name="cep" id="panel-admin-user-cep" inputmode="numeric" class="{{ $inputClass }}" value="{{ old('cep', $user->cep) }}" placeholder="00000-000">
            <small id="panel-admin-user-cep-status" class="text-xs text-slate-400">Preenchimento automático pelo ViaCEP.</small>
        </div>
        <div class="md:col-span-4">
            <label class="{{ $labelClass }}">Logradouro</label>
            <input name="street" id="panel-admin-user-street" class="{{ $inputClass }}" value="{{ old('street', $user->street) }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">Número</label>
            <input name="number" id="panel-admin-user-number" class="{{ $inputClass }}" value="{{ old('number', $user->number) }}">
        </div>
        <div class="md:col-span-4">
            <label class="{{ $labelClass }}">Complemento</label>
            <input name="complement" class="{{ $inputClass }}" value="{{ old('complement', $user->complement) }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">Bairro</label>
            <input name="neighborhood" id="panel-admin-user-neighborhood" class="{{ $inputClass }}" value="{{ old('neighborhood', $user->neighborhood) }}">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $labelClass }}">Cidade</label>
            <input name="city" id="panel-admin-user-city" class="{{ $inputClass }}" value="{{ old('city', $user->city) }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Estado</label>
            <input name="state" id="panel-admin-user-state" maxlength="2" class="{{ $inputClass }} uppercase" value="{{ old('state', $user->state) }}">
        </div>
    </div>
</section>

<section class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-5">
        <i class="fas fa-share-alt text-blue-600"></i>
        <h3 class="font-bold text-xl text-slate-800 dark:text-white">Redes sociais e privacidade</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach([
            'website' => 'Site', 'instagram' => 'Instagram', 'facebook' => 'Facebook',
            'twitter' => 'X / Twitter', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube',
        ] as $field => $label)
            <div>
                <label class="{{ $labelClass }}">{{ $label }}</label>
                <input name="{{ $field }}" class="{{ $inputClass }}" value="{{ old($field, $user->{$field}) }}">
            </div>
        @endforeach
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
        @foreach([
            'show_email_public' => 'Exibir e-mail no perfil público',
            'show_phone_public' => 'Exibir telefone no perfil público',
            'show_address_public' => 'Exibir endereço no perfil público',
            'hide_profile' => 'Ocultar perfil da comunidade',
        ] as $field => $label)
            <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-800 rounded-xl cursor-pointer">
                <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, (bool) $user->{$field}) ? 'checked' : '' }}
                    class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 dark:bg-slate-950">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</section>
