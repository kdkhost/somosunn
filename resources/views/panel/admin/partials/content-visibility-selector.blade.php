@php
    $field = $field ?? 'visibility';
    $selected = old($field, $selected ?? 'ambos');
    $title = $title ?? 'Visibilidade';
    $description = $description ?? 'Escolha onde este item pode aparecer para venda.';
    $options = [
        'ambos' => [
            'label' => 'Ambos os locais',
            'hint' => 'Disponivel na plataforma principal e no Somos Unnicas.',
            'badge' => 'UNN + Somos Unnicas',
        ],
        'somos_unn' => [
            'label' => 'Somente Somos UNN',
            'hint' => 'Fica visivel apenas na plataforma principal.',
            'badge' => 'Somente UNN',
        ],
        'somos_unicas' => [
            'label' => 'Exclusivo Somos Unnicas',
            'hint' => 'Fica visivel apenas no ambiente Somos Unnicas.',
            'badge' => 'Exclusivo',
        ],
    ];
@endphp

<div class="space-y-4">
    <div class="space-y-1">
        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">
            {{ $title }}
        </label>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 transition-colors">
            {{ $description }}
        </p>
    </div>

    <div class="space-y-3">
        @foreach($options as $value => $option)
            <label class="block cursor-pointer">
                <div class="flex items-start gap-3">
                    <input type="radio" name="{{ $field }}" value="{{ $value }}" class="peer sr-only"
                        @checked($selected === $value)>
                    <div
                        class="mt-4 flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-transparent transition-all peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white dark:border-slate-700 dark:bg-slate-900">
                        <i class="fas fa-check text-[10px]"></i>
                    </div>
                    <div
                        class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:shadow-blue-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:peer-checked:border-blue-500 dark:peer-checked:bg-blue-950/30">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-slate-800 dark:text-white transition-colors">
                                {{ $option['label'] }}
                            </span>
                            <span
                                class="inline-flex rounded-full bg-white px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500 ring-1 ring-slate-200 transition-colors dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                                {{ $option['badge'] }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400 transition-colors">
                            {{ $option['hint'] }}
                        </p>
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>
