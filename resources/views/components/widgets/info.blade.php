@props(['title', 'value', 'color' => 'gray'])
<div class="bg-white rounded-lg shadow p-4 flex items-center gap-4 border-l-4 border-{{$color}}-500">
    <div class="text-2xl text-{{$color}}-600">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="flex-1">
        <div class="font-semibold text-gray-800">{{ $title }}</div>
        <div class="text-2xl font-bold text-{{$color}}-700">{{ $value }}</div>
    </div>
</div>