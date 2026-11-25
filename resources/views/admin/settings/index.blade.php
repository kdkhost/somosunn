@extends('admin.layouts.app')

@php
    $groupLabels = [
        'general' => 'Geral',
        'appearance' => 'Aparência',
        'images' => 'Imagens',
        'player' => 'Player',
        'ads' => 'Anúncios',
        'pwa' => 'PWA',
        'marketplace' => 'Marketplace',
        'gateway' => 'Pagamentos',
        'smtp' => 'SMTP',
        'social' => 'Social Login',
        'seo' => 'SEO',
        'storage' => 'Armazenamento',
        'system' => 'Sistema',
    ];
    $currentLabel = $groupLabels[$group] ?? 'Configurações';
@endphp

@section('title', 'Configurações - ' . $currentLabel)
@section('page_title', 'Configurações — ' . $currentLabel)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings', ['group' => 'general']) }}">Configurações</a></li>
    <li class="breadcrumb-item active">{{ $currentLabel }}</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}">
    <style>
        .colorpicker-element .input-group-addon i,
        .colorpicker-element .input-group-append i {
            width: 16px;
            height: 16px;
            display: inline-block;
            cursor: pointer;
        }
        .settings-nav-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 1rem;
        }
        .settings-nav-pills .btn {
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
        }
    </style>
@endpush

@section('content')
    {{-- Navegação rápida e organizada entre todos os grupos de configuração --}}
    <div class="settings-nav-pills">
        @foreach($groupLabels as $slug => $label)
            <a href="{{ route('admin.settings', ['group' => $slug]) }}"
                class="btn btn-sm {{ $group === $slug ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" novalidate autocomplete="off">
        @csrf
        <input type="hidden" name="current_group" value="{{ $group }}">

        <div class="card card-outline card-primary shadow-sm mb-0">
            @include('admin.settings.partials.' . $group, ['settings' => $settings, 'getUrl' => $getUrl])

            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="fas fa-circle-question me-1"></i>Clique no ícone <strong>?</strong> ao lado de qualquer campo para abrir o passo a passo.</small>
                <button type="submit" id="btn-save-settings" class="btn btn-primary fw-bold px-4">
                    <i class="fas fa-save me-1"></i> <span id="btn-save-label">Salvar Alterações</span>
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
    <script>
        $(function () {
            $('.colorpicker-element').colorpicker();
            $('.colorpicker-element').on('colorpickerChange', function (event) {
                $(this).find('.fa-square').css('color', event.color.toString());
            });
            $('.mask-phone').inputmask('(99) 9999[9]-9999');
            $('.mask-cep').inputmask('99999-999');

            // AJAX Submit - salva sem recarregar a tela
            $('#settings-form').on('submit', function (e) {
                e.preventDefault();

                const form = this;
                const btn  = document.getElementById('btn-save-settings');
                const lbl  = document.getElementById('btn-save-label');
                const origLbl = lbl.innerHTML;

                btn.disabled = true;
                lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                })
                .then(async (response) => {
                    const text = await response.text();
                    let data;
                    try { data = JSON.parse(text); } catch (e) { data = { message: text }; }

                    if (response.ok) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message || 'Configurações salvas com sucesso.');
                        }
                    } else {
                        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Erro ao salvar');
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Erro de conexão. Tente novamente.');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    lbl.innerHTML = origLbl;
                });
            });
        });
    </script>
@endpush
