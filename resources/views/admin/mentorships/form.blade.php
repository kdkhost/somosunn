@extends('admin.layouts.app')

@section('page_title', $mentorship->exists ? 'Editar Mentoria' : 'Nova Mentoria')

@php
    $schedulePretty = '';
    if (!empty($mentorship->schedule) && is_array($mentorship->schedule)) {
        $schedulePretty = json_encode($mentorship->schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
@endphp

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <h5 class="mb-1">{{ $mentorship->exists ? 'Atualizar mentoria' : 'Cadastrar mentoria' }}</h5>
            <p class="text-muted mb-0">
                Preencha os dados principais. O campo de agenda aceita JSON para horarios, datas e links de reuniao.
            </p>
        </div>

        <form method="POST" action="{{ $mentorship->exists ? route('admin.mentorships.update', $mentorship) : route('admin.mentorships.store') }}">
            @csrf
            @if($mentorship->exists)
                @method('PUT')
            @endif

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="title">Titulo</label>
                    <input id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $mentorship->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="mentor_id">Mentor responsavel</label>
                    <select id="mentor_id" name="mentor_id" class="form-control @error('mentor_id') is-invalid @enderror">
                        @foreach(($mentors ?? collect()) as $mentor)
                            <option value="{{ $mentor->id }}" @selected((string) old('mentor_id', $mentorship->mentor_id) === (string) $mentor->id)>
                                {{ $mentor->name }} ({{ $mentor->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('mentor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="price">Preco (R$)</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $mentorship->price) }}">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="slots">Vagas</label>
                    <input id="slots" name="slots" type="number" min="1" class="form-control @error('slots') is-invalid @enderror" value="{{ old('slots', $mentorship->slots) }}">
                    @error('slots')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descricao</label>
                <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror" placeholder="Explique objetivo, publico e formato da mentoria">{{ old('description', $mentorship->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="schedule_json">Agenda (JSON opcional)</label>
                <textarea id="schedule_json" name="schedule_json" rows="8" class="form-control @error('schedule_json') is-invalid @enderror" placeholder='{"timezone":"America/Sao_Paulo","sessions":[{"date":"2026-03-10","time":"19:00","link":"https://meet.google.com/..."}] }'>{{ old('schedule_json', $schedulePretty) }}</textarea>
                @error('schedule_json')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Dica: valide o JSON antes de salvar para facilitar uso no site e API.</small>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.mentorships.index') }}" class="btn btn-outline-secondary">Voltar</a>
                <button class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Salvar mentoria
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
