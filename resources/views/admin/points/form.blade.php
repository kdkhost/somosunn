@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h3>{{ $rule->id ? 'Editar' : 'Nova' }} Regra</h3>

    <form method="POST" action="{{ $rule->id ? route('admin.points-rules.update', $rule) : route('admin.points-rules.store') }}">
        @csrf
        @if($rule->id)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Key</label>
            <input type="text" name="key" class="form-control" value="{{ old('key', $rule->key) }}" {{ $rule->id ? 'readonly' : '' }}>
        </div>

        <div class="mb-3">
            <label class="form-label">Rótulo</label>
            <input type="text" name="label" class="form-control" value="{{ old('label', $rule->label) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Pontos</label>
            <input type="number" name="points" class="form-control" value="{{ old('points', $rule->points) }}">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input" {{ old('active', $rule->active) ? 'checked' : '' }}>
            <label for="active" class="form-check-label">Ativa</label>
        </div>

        <button class="btn btn-primary">{{ __('messages.buttons.save') }}</button>
        <a href="{{ route('admin.points-rules.index') }}" class="btn btn-secondary">{{ __('messages.buttons.cancel') }}</a>
    </form>
</div>
@endsection