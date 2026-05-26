@php
    $exhibitorStatus = $event->exists
        ? $event->exhibitorSalesStatus()
        : ['label' => 'Inativo', 'badge' => 'secondary'];
    $exhibitorBadgeClass = match($exhibitorStatus['badge'] ?? 'secondary') {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200',
        'warning' => 'bg-yellow-100 text-yellow-900 dark:bg-yellow-500/15 dark:text-yellow-100',
        'dark' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-100',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    };
    $exhibitorSoldSlots = $event->exists ? (int) $event->confirmed_exhibitor_slots : 0;
    $exhibitorRemainingSlots = $event->exists
        ? (int) $event->remaining_exhibitor_slots
        : (int) old('exhibitor_total_slots', $event->exhibitor_total_slots ?? 0);
    $exhibitorMoneyValue = function (string $field) use ($event) {
        $oldValue = old($field);
        if ($oldValue !== null) {
            return $oldValue;
        }

        $value = $event->{$field} ?? null;

        return filled($value) ? number_format((float) $value, 2, ',', '.') : '';
    };
@endphp

<div x-show="tab === 'exhibitors'" class="max-w-5xl space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-[0.22em] text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                    <i class="fas fa-store"></i>
                    Venda dentro do evento
                </div>
                <h2 class="mt-4 text-2xl font-black text-slate-950 dark:text-white">Areas para expositores</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500 dark:text-slate-400">
                    Ative apenas quando este evento vender espacos para marcas. No checkout publico, o cliente escolhe entre participar normalmente ou comprar uma area de expositor.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-widest {{ $exhibitorBadgeClass }}">
                    {{ $exhibitorStatus['label'] ?? 'Inativo' }}
                </span>
                @if($event->exists && auth()->user()?->canManageEventExhibitors($event))
                    <a href="{{ route('panel.admin.events.exhibitors.index', $event) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-black text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-500/10 dark:text-blue-200">
                        <i class="fas fa-list-check"></i>
                        Inscricoes
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500">Total de areas</p>
                <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ (int) old('exhibitor_total_slots', $event->exhibitor_total_slots ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500">Vendidas/reservadas</p>
                <p class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ $exhibitorSoldSlots }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500">Restantes</p>
                <p class="mt-2 text-3xl font-black text-blue-700 dark:text-blue-300">{{ $exhibitorRemainingSlots }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                        <input type="checkbox" name="exhibitor_sales_enabled" value="1" @checked(old('exhibitor_sales_enabled', $event->exhibitor_sales_enabled))
                            class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block text-sm font-black text-slate-900 dark:text-white">Vender areas</span>
                            <span class="mt-1 block text-xs font-medium leading-5 text-slate-500 dark:text-slate-400">Se desligado, o publico nao ve a opcao de expositor.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                        <input type="hidden" name="exhibitor_show_publicly" value="0">
                        <input type="checkbox" name="exhibitor_show_publicly" value="1" @checked(old('exhibitor_show_publicly', $event->exists ? ($event->exhibitor_show_publicly ?? true) : true))
                            class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block text-sm font-black text-slate-900 dark:text-white">Exibir no site</span>
                            <span class="mt-1 block text-xs font-medium leading-5 text-slate-500 dark:text-slate-400">Mantem a oferta visivel quando houver lote e vagas.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                        <input type="checkbox" name="exhibitor_includes_ticket" value="1" @checked(old('exhibitor_includes_ticket', $event->exhibitor_includes_ticket))
                            class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block text-sm font-black text-slate-900 dark:text-white">Ingresso incluso</span>
                            <span class="mt-1 block text-xs font-medium leading-5 text-slate-500 dark:text-slate-400">Beneficio informativo, sem consumir ingressos normais.</span>
                        </span>
                    </label>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500">Quantidade total de areas</label>
                        <input type="number" name="exhibitor_total_slots" min="0" max="100000" value="{{ old('exhibitor_total_slots', $event->exhibitor_total_slots) }}"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="Ex: 20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500">Imagem, planta ou mapa</label>
                        <input type="file" name="exhibitor_area_image" accept="image/*"
                            class="filepond mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                        @if($event->exhibitor_area_image_url)
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                <a href="{{ $event->exhibitor_area_image_url }}" target="_blank" class="font-black text-blue-700 dark:text-blue-300">
                                    Ver imagem atual
                                </a>
                                <label class="inline-flex items-center gap-2 font-bold text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" name="remove_exhibitor_area_image" value="1" class="rounded border-slate-300 text-red-600">
                                    Remover atual
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <label class="block">
                        <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">Descricao publica da area</span>
                        <textarea name="exhibitor_description" rows="6"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="Explique o que o expositor recebe, metragem, beneficios e regras principais.">{{ old('exhibitor_description', $event->exhibitor_description) }}</textarea>
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">Observacoes internas</span>
                        <textarea name="exhibitor_internal_notes" rows="6"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="Informacoes administrativas, combinados e restricoes internas.">{{ old('exhibitor_internal_notes', $event->exhibitor_internal_notes) }}</textarea>
                    </label>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white">Lotes de area para expositor</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Configure preco, data limite e quantidade opcional por lote.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @for($i = 1; $i <= 3; $i++)
                        @php
                            $deadlineField = 'exhibitor_batch_' . $i . '_deadline';
                            $slotsField = 'exhibitor_batch_' . $i . '_slots';
                            $priceField = 'exhibitor_batch_' . $i . '_price';
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60">
                            <p class="mb-4 text-sm font-black uppercase tracking-widest text-slate-500">{{ $i }} lote</p>
                            <div class="grid gap-4 md:grid-cols-3">
                                <label>
                                    <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">Valor</span>
                                    <div class="relative mt-2">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">R$</span>
                                        <input type="text" name="{{ $priceField }}" value="{{ $exhibitorMoneyValue($priceField) }}"
                                            class="mask-money w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm font-black text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                            placeholder="0,00">
                                    </div>
                                </label>
                                <label>
                                    <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">Data limite</span>
                                    <input type="datetime-local" name="{{ $deadlineField }}"
                                        value="{{ old($deadlineField, $event->{$deadlineField} ? $event->{$deadlineField}->format('Y-m-d\TH:i') : '') }}"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                </label>
                                <label>
                                    <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">Limite de areas</span>
                                    <input type="number" min="0" max="100000" name="{{ $slotsField }}"
                                        value="{{ old($slotsField, $event->{$slotsField}) }}"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                        placeholder="Opcional">
                                </label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-blue-100 bg-blue-50 p-6 dark:border-blue-900/60 dark:bg-blue-500/10">
                <h3 class="text-sm font-black uppercase tracking-widest text-blue-700 dark:text-blue-200">Como aparece no site</h3>
                <div class="mt-5 space-y-3 text-sm font-semibold text-blue-900 dark:text-blue-100">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle mt-1 text-blue-600 dark:text-blue-300"></i>
                        <span>O comprador escolhe entre participar do evento ou comprar area de expositor na tela de checkout.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle mt-1 text-blue-600 dark:text-blue-300"></i>
                        <span>Quando acabar a quantidade total ou o lote ativo, a opcao de expositor some automaticamente.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle mt-1 text-blue-600 dark:text-blue-300"></i>
                        <span>Ingresso normal e area de expositor usam estoques separados.</span>
                    </div>
                </div>
            </div>

            @if($event->exhibitor_area_image_url)
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <img src="{{ $event->exhibitor_area_image_url }}" alt="Imagem da area de expositores" class="h-56 w-full object-cover">
                    <div class="p-5">
                        <p class="text-sm font-black text-slate-900 dark:text-white">Imagem atual da area</p>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Sera exibida na oferta publica do expositor.</p>
                    </div>
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">Checklist</h3>
                <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-600 dark:text-slate-300">
                    <li class="flex items-center gap-2"><i class="fas fa-toggle-on text-blue-600"></i> Ativar venda</li>
                    <li class="flex items-center gap-2"><i class="fas fa-users text-blue-600"></i> Informar quantidade total</li>
                    <li class="flex items-center gap-2"><i class="fas fa-money-bill-wave text-blue-600"></i> Definir ao menos um lote com valor</li>
                    <li class="flex items-center gap-2"><i class="fas fa-eye text-blue-600"></i> Marcar exibicao publica</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
