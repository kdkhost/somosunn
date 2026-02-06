@extends('admin.layouts.app')

@section('page_title', ($faq->id ? 'Editar' : 'Nova').' pergunta')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.faqs.index') }}" data-pjax>FAQ</a></li>
    <li class="breadcrumb-item active">{{ $faq->id ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="{{ $faq->id ? route('admin.faqs.update',$faq) : route('admin.faqs.store') }}">
            @csrf
            @if($faq->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Contexto</label>
                    <select name="context" class="form-control" required>
                        @foreach($contexts as $key => $label)
                            <option value="{{ $key }}" {{ old('context', $faq->context ?: 'general') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Define onde essa pergunta aparece no site.</small>
                </div>

                <div class="form-group col-md-4">
                    <label>Ordem</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" min="0" max="999999">
                    <small class="text-muted">Menor aparece primeiro.</small>
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $faq->is_active ?? true) ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ !old('is_active', $faq->is_active ?? true) ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Pergunta</label>
                <input type="text" name="question" class="form-control" required maxlength="255" value="{{ old('question', $faq->question) }}" placeholder="Ex: Posso cancelar a qualquer momento?">
            </div>

            <div class="form-group">
                <label>Resposta</label>
                <textarea name="answer" class="form-control" rows="6" required placeholder="Digite a resposta...">{{ old('answer', $faq->answer) }}</textarea>
                <small class="text-muted">Dica: quebras de linha são mantidas no site.</small>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

