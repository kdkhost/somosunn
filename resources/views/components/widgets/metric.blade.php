@props(['title', 'icon', 'value', 'color' => 'blue', 'realtime' => false])
<div class="bg-white rounded-lg shadow p-4 flex items-center gap-4 border-l-4 border-{{$color}}-500">
    <div class="text-2xl text-{{$color}}-600">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="flex-1">
        <div class="font-semibold text-gray-800">{{ $title }}</div>
        <div class="text-2xl font-bold text-{{$color}}-700" @if($realtime) x-data="{ value: {{ $value }} }" x-text="value" @endif>
            {{ $value }}
        </div>
    </div>
</div>