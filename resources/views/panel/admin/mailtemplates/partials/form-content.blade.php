<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nome</label>
                    <input type="text" name="name" id="tpl_name" value="{{ old('name', $template->name ?? '') }}" required
                           class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800 placeholder-slate-400">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Slug (Identificador)</label>
                    <input type="text" name="slug" id="tpl_slug" value="{{ old('slug', $template->slug ?? '') }}" required
                           {{ isset($template->id) ? 'readonly' : '' }}
                           class="w-full rounded-xl border-slate-400 bg-slate-100 text-slate-500 focus:border-blue-500 focus:ring-blue-500 {{ isset($template->id) ? 'cursor-not-allowed' : '' }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Assunto do E-mail</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" required
                       class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800 placeholder-slate-400">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Conteúdo (HTML)</label>
                <textarea name="body" id="bodyEditor">{{ old('body', $template->body ?? '') }}</textarea>
                <p class="text-xs text-slate-500 mt-2">A logo da plataforma será inserida automaticamente no topo do e-mail.</p>
            </div>
        </div>

        <!-- Variables -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-4">Variáveis Disponíveis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @php
                    $vars = [
                        ['{{user.name}}','Nome do usuário'],
                        ['{{user.email}}','E-mail do usuário'],
                        ['{{site.name}}','Nome do site'],
                        ['{{site.url}}','URL do site'],
                        ['{{order.id}}','ID do pedido'],
                        ['{{order.total}}','Total do pedido'],
                        ['{{order.status}}','Status do pedido'],
                        ['{{payment.link}}','Link de pagamento'],
                        ['{{event.title}}','Nome do evento'],
                        ['{{course.title}}','Nome do curso'],
                        ['{{mentorship.title}}','Nome da mentoria']
                    ];
                @endphp
                @foreach($vars as [$v, $d])
                    <button type="button" class="insert-var text-left p-3 rounded-xl border border-slate-300 bg-slate-50 hover:bg-blue-50 hover:border-blue-400 transition group" data-var="{{ $v }}">
                        <div class="font-mono text-xs font-bold text-blue-700">{{ $v }}</div>
                        <div class="text-xs text-slate-500 group-hover:text-blue-800">{{ $d }}</div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar Settings -->
    <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-bold text-slate-800 mb-2">Configurações</h3>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Categoria</label>
                <select name="category" class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">
                    @foreach(['sistema', 'conta', 'financeiro', 'marketing'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $template->category ?? '') == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">
                    <option value="1" {{ old('is_active', $template->is_active ?? true) ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ old('is_active', $template->is_active ?? true) ? '' : 'selected' }}>Inativo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Idioma</label>
                <input type="text" name="locale" value="{{ old('locale', $template->locale ?? 'pt-BR') }}"
                       class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">
            </div>
        </div>

        <!-- Test Email -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-bold text-slate-800 mb-2">Testar Template</h3>
            <p class="text-xs text-slate-500">Salve as alterações antes de testar para visualizar o conteúdo atualizado.</p>
            
            <div class="flex gap-2">
                 <input type="email" id="test_email_input" placeholder="seu@email.com" 
                        class="flex-1 rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-sm">
                 <button type="button" id="btnSendTest" 
                         data-url="{{ isset($template->id) ? route('panel.admin.mailtemplates.sendpreview', $template) : '' }}"
                         class="bg-slate-800 text-white rounded-xl px-4 hover:bg-slate-900 transition shadow-md">
                     <i class="fas fa-paper-plane"></i>
                 </button>
            </div>
        </div>
    </div>
</div>
