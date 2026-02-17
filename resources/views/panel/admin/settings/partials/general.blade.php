<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Company Info -->
    <div class="md:col-span-2">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-building text-blue-500"></i> Informações da Empresa
        </h3>
    </div>

    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nome do Site (App Name)</label>
        <input type="text" name="app_name" value="{{ $settings['app_name'] ?? config('app.name') }}"
            class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Razão Social / Nome da
            Empresa</label>
        <input type="text" name="company_name" value="{{ $settings['company_name'] ?? '' }}"
            class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Telefone / WhatsApp</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-phone text-slate-400"></i>
            </div>
            <input type="text" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}"
                data-inputmask="'mask': ['(99) 9999-9999', '(99) 99999-9999']"
                class="pl-10 w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">E-mail de Contato</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-envelope text-slate-400"></i>
            </div>
            <input type="email" name="company_email" value="{{ $settings['company_email'] ?? '' }}"
                class="pl-10 w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>

    <!-- Address -->
    <div class="md:col-span-2 mt-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-blue-500"></i> Endereço
        </h3>
    </div>

    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">CEP</label>
        <input type="text" name="company_zip" value="{{ $settings['company_zip'] ?? '' }}"
            data-inputmask="'mask': '99999-999'"
            class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
    </div>

    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Endereço (Rua/Av)</label>
            <input type="text" name="company_address" value="{{ $settings['company_address'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Número</label>
            <input type="text" name="company_number" value="{{ $settings['company_number'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>

    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Complemento</label>
            <input type="text" name="company_complement" value="{{ $settings['company_complement'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Bairro</label>
            <input type="text" name="company_district" value="{{ $settings['company_district'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>

    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cidade</label>
            <input type="text" name="company_city" value="{{ $settings['company_city'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Estado (UF)</label>
            <input type="text" name="company_state" value="{{ $settings['company_state'] ?? '' }}"
                class="w-full rounded-2xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Inputmask !== 'undefined') {
                Inputmask().mask(document.querySelectorAll('input'));
            }
        });
    </script>
@endpush