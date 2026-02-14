<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm font-bold text-slate-500">{{ $title }}</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $value }}</div>
        </div>
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $iconBg }}">
            <i class="{{ $icon }} text-xl {{ $iconColor }}"></i>
        </div>
    </div>
    <div class="mt-4 text-sm text-slate-600">
        {{ $slot }}
    </div>
</div>
