@props(['title', 'icon', 'items' => [], 'color' => 'green'])
<div class="bg-white rounded-lg shadow p-4 border-l-4 border-{{$color}}-500">
    <div class="flex items-center gap-3 mb-2">
        <div class="text-xl text-{{$color}}-600">
            <i class="{{ $icon }}"></i>
        </div>
        <div class="font-semibold text-gray-800">{{ $title }}</div>
    </div>
    <ul class="text-gray-700 text-sm space-y-1">
        @foreach($items as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
</div>