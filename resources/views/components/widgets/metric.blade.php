@props(['title', 'icon', 'value', 'color' => 'blue', 'realtime' => false])
<div
    class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-{{$color}}-500 dark:border-{{$color}}-400 transition-colors duration-300">
    <div
        class="w-12 h-12 rounded-xl bg-{{$color}}-50 dark:bg-{{$color}}-900/20 text-{{$color}}-600 dark:text-{{$color}}-400 flex items-center justify-center shrink-0">
        <i class="{{ $icon }} text-xl"></i>
    </div>
    <div class="flex-1 min-w-0">
        <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 truncate">
            {{ $title }}</div>
        <div class="text-2xl font-black text-slate-900 dark:text-white" @if($realtime) x-data="{ value: {{ $value }} }"
        x-text="value" @endif>
            {{ $value }}
        </div>
    </div>
</div>