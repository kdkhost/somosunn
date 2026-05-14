@if($size === 'sm')
    {{-- Compact: icon + score --}}
    <span class="inline-flex items-center gap-1 text-xs font-bold" style="color: {{ $color }}" title="{{ $tooltipText() }}">
        <i class="fas {{ $iconClass() }}"></i>
        <span>{{ $score }}</span>
    </span>
@elseif($size === 'md')
    {{-- Medium: icon + score + label com borda --}}
    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold border" style="color: {{ $color }}; border-color: {{ $color }}20" title="{{ $tooltipText() }}">
        <i class="fas {{ $iconClass() }}"></i>
        <span>{{ $score }}</span>
        <span class="text-[10px] opacity-75">{{ $label }}</span>
    </span>
@elseif($size === 'lg')
    {{-- Large: icon + score + label + progress bar --}}
    <div class="inline-flex flex-col gap-1" title="{{ $tooltipText() }}">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-sm font-bold border" style="color: {{ $color }}; border-color: {{ $color }}20">
            <i class="fas {{ $iconClass() }}"></i>
            <span>{{ $score }}</span>
            <span class="text-xs opacity-75">{{ $label }}</span>
        </span>
        <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $score }}%; background-color: {{ $color }}"></div>
        </div>
    </div>
@endif
