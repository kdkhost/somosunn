@props(['title', 'icon', 'value', 'color' => 'blue', 'realtime' => false, 'href' => null])
<div
    class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-{{$color}}-500 dark:border-{{$color}}-400 transition-all duration-300 relative group {{ $href ? 'hover:shadow-md hover:-translate-y-0.5' : '' }}">
    <div
        class="w-12 h-12 rounded-xl bg-{{$color}}-50 dark:bg-{{$color}}-900/20 text-{{$color}}-600 dark:text-{{$color}}-400 flex items-center justify-center shrink-0">
        <i class="{{ $icon }} text-xl"></i>
    </div>
    <div class="flex-1 min-w-0">
        <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 truncate">
            {{ $title }}
        </div>
        <div class="text-2xl font-black text-slate-900 dark:text-white" @if($realtime) x-data="{ value: {{ $value }} }"
        x-text="value" @endif>
            {{ $value }}
        </div>
    </div>
    @if($href)
        <a href="{{ $href }}" class="absolute top-4 right-4 text-slate-300 hover:text-{{$color}}-500 transition-colors"
            title="Acessar">
            <i class="fas fa-arrow-up-right-from-square text-xs"></i>
        </a>
    @endif
</div>