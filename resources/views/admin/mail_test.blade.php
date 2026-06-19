@extends('admin.layouts.app')

@section('page_title', 'Teste de E-mail')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-envelope mr-1"></i> Enviar teste por template
            </h3>
        </div>
        <form method="POST" action="{{ route('admin.mailtest.send') }}">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="to">Destinatário</label>
                    <input type="email" id="to" name="to" class="form-control" value="{{ old('to') }}" required>
                </div>

                <div class="form-group">
                    <label for="template_slug">Template</label>
                    <select id="template_slug" name="template_slug" class="form-control">
                        <option value="smtp_test">Teste SMTP padrão (smtp_test)</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->slug }}" {{ old('template_slug') === $template->slug ? 'selected' : '' }}>
                                {{ $template->category ?: 'sistema' }} - {{ $template->name }} ({{ $template->slug }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Assunto e conteúdo devem ser editados em Templates de E-mail.</small>
                </div>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar teste
                </button>
            </div>
        </form>
    </div>
@endsection
