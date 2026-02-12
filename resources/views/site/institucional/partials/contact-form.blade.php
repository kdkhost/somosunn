<div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl">
    <h2 class="text-2xl font-black text-gray-900 mb-6">Envie uma mensagem</h2>

    <form id="contact-form" action="{{ route('contato.send') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nome completo</label>
            <input type="text" name="name" required
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                   style="--tw-ring-color: var(--unn-azul-1)"
                   placeholder="Seu nome" value="{{ old('name') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
            <input type="email" name="email" required
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                   style="--tw-ring-color: var(--unn-azul-1)"
                   placeholder="seu@email.com" value="{{ old('email') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
            <input type="tel" name="phone"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                   style="--tw-ring-color: var(--unn-azul-1)"
                   placeholder="(00) 00000-0000" value="{{ old('phone') }}">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Assunto</label>
            <select name="subject" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                    style="--tw-ring-color: var(--unn-azul-1)">
                <option value="">Selecione um assunto</option>
                <option value="duvidas" {{ old('subject') === 'duvidas' ? 'selected' : '' }}>Dúvidas sobre a plataforma</option>
                <option value="parcerias" {{ old('subject') === 'parcerias' ? 'selected' : '' }}>Propostas de parceria</option>
                <option value="suporte" {{ old('subject') === 'suporte' ? 'selected' : '' }}>Suporte técnico</option>
                <option value="comercial" {{ old('subject') === 'comercial' ? 'selected' : '' }}>Departamento comercial</option>
                <option value="imprensa" {{ old('subject') === 'imprensa' ? 'selected' : '' }}>Assessoria de imprensa</option>
                <option value="outro" {{ old('subject') === 'outro' ? 'selected' : '' }}>Outro assunto</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Mensagem <span class="text-gray-400 font-normal">(mínimo 10 caracteres)</span></label>
            <textarea name="message" id="contact-message" rows="5" required minlength="10" maxlength="4000"
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition resize-none"
                      style="--tw-ring-color: var(--unn-azul-1)"
                      placeholder="Como podemos ajudar? (mínimo 10 caracteres)">{{ old('message') }}</textarea>
            <div class="flex justify-between mt-1 text-xs text-gray-500">
                <span id="char-counter" class="text-red-500">0/10 caracteres</span>
                <span id="char-max">Máximo: 4000</span>
            </div>
        </div>

        <button type="submit" id="submit-btn" disabled
            class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-paper-plane"></i>
            Enviar mensagem
        </button>
    </form>
</div>

