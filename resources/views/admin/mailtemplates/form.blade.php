@extends('admin.layouts.app')

@section('page_title', ($template->id ? 'Editar' : 'Novo').' template de e-mail')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.mailtemplates.index') }}" data-pjax>Templates</a></li>
    <li class="breadcrumb-item active">{{ $template->id ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $template->id ? route('admin.mailtemplates.update', $template) : route('admin.mailtemplates.store') }}" class="ajax-form">
            @csrf
            @if($template->id)
                @method('PUT')
            @endif
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Nome</label>
                <input type="text" name="name" id="tpl_name" class="form-control" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="form-group col-md-4">
                <label>Slug</label>
                <input type="text" name="slug" id="tpl_slug" class="form-control" value="{{ old('slug', $template->slug) }}" {{ $template->id ? 'readonly' : '' }} required>
            </div>
            <div class="form-group col-md-4">
                <label>Assunto</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}" required>
            </div>
        </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Categoria</label>
                    <select name="category" class="form-control">
                        @foreach(['conta','financeiro','marketing','sistema'] as $cat)
                            <option value="{{ $cat }}" {{ old('category',$template->category)==$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $template->is_active) ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ !old('is_active', $template->is_active) ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Idioma</label>
                    <input type="text" name="locale" class="form-control" value="{{ old('locale', $template->locale ?? 'pt-BR') }}">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button type="button" id="btnSendTest" class="btn btn-outline-primary btn-block" data-url="{{ route('admin.mailtemplates.sendpreview', $template->id ?: 0) }}">Enviar teste</button>
                </div>
            </div>

        <div class="form-row">
            <div class="col-lg-8">
                <div class="form-group">
                    <label>Corpo (HTML)</label>
                    <textarea name="body" id="bodyEditor" class="form-control" rows="14">{{ old('body', $template->body) }}</textarea>
                    <small class="text-muted">A logo da UNN será inserida automaticamente no cabeçalho.</small>
                </div>

                <div class="mb-3">
                    <label>Variáveis disponíveis (clique para inserir)</label>
                    @php
                        $vars = [
                            ['{{user.name}}','Nome completo do usuário'],
                            ['{{user.email}}','E-mail do usuário'],
                            ['{{user.phone}}','Telefone do usuário'],
                            ['{{user.level}}','Nível / perfil do usuário'],
                            ['{{user.points}}','Pontuação atual'],
                            ['{{site.name}}','Nome do site'],
                            ['{{site.url}}','URL do site'],
                            ['{{site.support_email}}','E-mail de suporte'],
                            ['{{site.logo}}','Logo do site'],
                            ['{{order.id}}','ID do pedido'],
                            ['{{order.total}}','Total do pedido'],
                            ['{{order.status}}','Status do pedido'],
                            ['{{order.date}}','Data do pedido'],
                            ['{{payment.due_date}}','Vencimento do pagamento'],
                            ['{{payment.link}}','Link de pagamento'],
                            ['{{event.title}}','Título do evento'],
                            ['{{event.date}}','Data do evento'],
                            ['{{event.link}}','Link do evento'],
                            ['{{course.title}}','Título do curso'],
                            ['{{mentorship.title}}','Título da mentoria']
                        ];
                    @endphp
                    <div class="row">
                        @foreach($vars as [$v,$d])
                            <div class="col-sm-6 col-md-4 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-var w-100 text-left" data-var="{{ $v }}">
                                    <strong>{{ $v }}</strong><br><small class="text-muted">{{ $d }}</small>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Preview em tempo real</strong></div>
                    <div class="card-body p-0" id="tpl_preview" style="min-height:200px; background:#f4f6f9;"></div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('admin.mailtemplates.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>
        </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
$(function(){
    $('#bodyEditor').summernote({height:420});
    $('.insert-var').on('click', function(){
        const v = $(this).data('var');
        $('#bodyEditor').summernote('pasteHTML', v);
    });
    $('#btnSendTest').on('click', function(){
        const url = $(this).data('url');
        const email = prompt('Enviar prévia para qual e-mail?');
        if(!email) return;
        $.post(url, {_token:'{{ csrf_token() }}', email: email}, function(){ toastr.success('Prévia enviada'); });
    });

    // auto slug (somente quando não existe)
    @if(!$template->id)
    $('#tpl_name').on('keyup change', function(){
        if($('#tpl_slug').val().trim() !== '') return;
        const slug = $(this).val().toString()
            .normalize('NFD').replace(/[\\u0300-\\u036f]/g,'')
            .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
        $('#tpl_slug').val(slug);
    });
    @endif

    function renderPreview(){
        @php
            // Fetch dynamic settings for JS preview
            $logo = \App\Models\Setting::where('key', 'logo_admin')->value('value');
            if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_front')->value('value');
            if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_image')->value('value');
            $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');
            
            $primaryColor = \App\Models\Setting::where('key', 'site_color_primary')->value('value') ?? '#007bff';
            $secondaryColor = \App\Models\Setting::where('key', 'site_color_secondary')->value('value') ?? '#6c757d';
        @endphp

        const logo = '{{ $logoUrl }}';
        const primaryColor = '{{ $primaryColor }}';
        const secondaryColor = '{{ $secondaryColor }}';
        const siteName = '{{ config('app.name') }}';
        const siteUrl = '{{ url('/') }}';
        const year = '{{ date('Y') }}';
        
        const body = $('#bodyEditor').summernote('code');
        
        // Use the same layout structure as the backend, but scaled for sidebar
        $('#tpl_preview').html(`
        <div style="background-color: #ffffff; width: 100%; font-family: sans-serif; height: 100%; box-sizing: border-box; display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%); padding: 25px 20px; text-align: center; flex-shrink: 0;">
                <img src="${logo}" alt="${siteName}" style="max-height: 50px; max-width: 100%; height: auto;">
            </div>
            
            <!-- Body -->
            <div style="padding: 25px 20px; color: #333333; line-height: 1.6; word-wrap: break-word; font-size: 14px; flex-grow: 1;">
                ${body}
            </div>
            
            <!-- Footer -->
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; color: #777777; font-size: 11px; border-top: 1px solid #eeeeee; flex-shrink: 0;">
                <p style="margin: 2px 0;">&copy; ${year} ${siteName}.</p>
                <p style="margin: 2px 0;"><a href="${siteUrl}" style="color: ${primaryColor}; text-decoration: none;">Visite nosso site</a></p>
            </div>
        </div>
        `);
    }
    renderPreview();
    $('#bodyEditor').on('summernote.change', renderPreview);
});
</script>
@endpush
