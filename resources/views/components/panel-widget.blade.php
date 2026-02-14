<div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-5 transition-all duration-200 hover:shadow-2xl hover:-translate-y-1 group cursor-pointer">
    <div class="flex items-center justify-between gap-2">
        <div>
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400 group-hover:text-[#1F5EDB] transition">{{ $title }}</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1 group-hover:text-[#1F5EDB] transition">{!! $value !!}</div>
        </div>
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $iconBg }} shadow group-hover:scale-110 transition-transform">
            <i class="{{ $icon }} text-2xl {{ $iconColor }}"></i>
        </div>
    </div>
    <div class="mt-4 text-sm text-slate-600 group-hover:text-slate-800 transition">
        {{ $slot }}
    </div>
</div>
