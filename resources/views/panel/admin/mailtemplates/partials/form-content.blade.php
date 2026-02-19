<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Nome</label>
                    <input type="text" name="name" id="tpl_name" value="{{ old('name', $template->name ?? '') }}" required
                           class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Slug (Identificador)</label>
                    <input type="text" name="slug" id="tpl_slug" value="{{ old('slug', $template->slug ?? '') }}" required
                           {{ isset($template->id) ? 'readonly' : '' }}
                           class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 outline-none transition-all font-medium {{ isset($template->id) ? 'cursor-not-allowed opacity-75' : 'focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10' }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Assunto do E-mail</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" required
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Conteúdo (HTML)</label>
                <textarea name="body" id="bodyEditor">{{ old('body', $template->body ?? '') }}</textarea>
                <p class="text-xs text-slate-500 dark:text-slate-500 mt-2 transition-colors">A logo da plataforma será inserida automaticamente no topo do e-mail.</p>
            </div>
        </div>

        <!-- Variables -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fas fa-magic"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Variáveis Dinâmicas</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 transition-colors">Clique para inserir no editor.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $vars = [
                        ['{{user.name}}','Nome do usuário', 'fa-user'],
                        ['{{user.email}}','E-mail do usuário', 'fa-envelope'],
                        ['{{site.name}}','Nome do site', 'fa-globe'],
                        ['{{site.url}}','URL do site', 'fa-link'],
                        ['{{order.id}}','ID do pedido', 'fa-shopping-cart'],
                        ['{{order.total}}','Total do pedido', 'fa-dollar-sign'],
                        ['{{order.status}}','Status do pedido', 'fa-info-circle'],
                        ['{{payment.link}}','Link de pagamento', 'fa-credit-card'],
                        ['{{event.title}}','Nome do evento', 'fa-calendar'],
                        ['{{course.title}}','Nome do curso', 'fa-graduation-cap'],
                        ['{{mentorship.title}}','Nome da mentoria', 'fa-user-tie']
                    ];
                @endphp
                @foreach($vars as [$v, $d, $icon])
                    <button type="button" class="insert-var text-left p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 hover:shadow-sm transition group" data-var="{{ $v }}">
                        <div class="flex items-center gap-3">
                            <i class="fas {{ $icon }} text-slate-300 dark:text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                            <div>
                                <div class="font-mono text-[11px] font-bold text-blue-600 dark:text-blue-400 mb-0.5">{{ $v }}</div>
                                <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight group-hover:text-slate-600 dark:group-hover:text-slate-300">{{ $d }}</div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar Settings -->
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="font-bold text-slate-800 dark:text-white mb-2">Configurações</h3>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Categoria</label>
                <select name="category" class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    @foreach(['sistema', 'conta', 'financeiro', 'marketing'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $template->category ?? '') == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Status</label>
                <select name="is_active" class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="1" {{ old('is_active', $template->is_active ?? true) ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ old('is_active', $template->is_active ?? true) ? '' : 'selected' }}>Inativo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Idioma</label>
                <input type="text" name="locale" value="{{ old('locale', $template->locale ?? 'pt-BR') }}"
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
        </div>

        <!-- Test Email -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4 transition-colors duration-300">
            <h3 class="font-bold text-slate-800 dark:text-white mb-2 transition-colors">Testar Template</h3>
            <p class="text-xs text-slate-500 dark:text-slate-500 transition-colors">Salve as alterações antes de testar para visualizar o conteúdo atualizado.</p>
            
            <div class="flex gap-2">
                 <input type="email" id="test_email_input" placeholder="seu@email.com" 
                        class="flex-1 px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm dark:text-white placeholder-slate-400 dark:placeholder-slate-600 transition-all">
                 <button type="button" id="btnSendTest" 
                         data-url="{{ isset($template->id) ? route('panel.admin.mailtemplates.sendpreview', $template) : '' }}"
                         class="bg-blue-600 text-white rounded-2xl px-6 hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                     <i class="fas fa-paper-plane"></i>
                 </button>
            </div>
        </div>
    </div>
</div>
