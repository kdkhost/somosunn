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
    </style>
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Configurações <small class="text-muted">&gt; {{ $currentLabel }}</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" novalidate autocomplete="off">
                @csrf
                <input type="hidden" name="current_group" value="{{ $group }}">

                <div class="card card-outline card-primary">
                    @include('admin.settings.partials.' . $group, ['settings' => $settings, 'getUrl' => $getUrl])

                    <div class="card-footer">
                        <button type="submit" id="btn-save-settings" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> <span id="btn-save-label">Salvar Alterações</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
