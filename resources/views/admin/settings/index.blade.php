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
        'system' => 'Sistema',
    ];
    $currentLabel = $groupLabels[$group] ?? 'Configurações';

    // Helper closure for URLs
    $getUrl = function ($key) use ($settings) {
        $val = $settings[$key] ?? null;
        if (!$val)
            return '';
        if (filter_var($val, FILTER_VALIDATE_URL))
            return $val;

        // Se o valor já começar com 'storage/', usa direto no asset
        if (str_starts_with($val, 'storage/')) {
            return asset($val);
        }

        // Se começar com 'uploads/', é provável que esteja na raiz pública (legacy ou custom)
        if (str_starts_with($val, 'uploads/')) {
            return asset($val);
        }

        // Fallback genérico para storage (padrão Laravel)
        return asset('storage/' . $val);
    };
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
                    <h1 class="m-0 text-dark">Configurações <small class="text-muted">> {{ $currentLabel }}</small></h1>
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
            {{-- Redundant alerts removed as toastr is global in app.blade.php --}}

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="current_group" value="{{ $group }}">

                <div class="card card-outline card-primary">
                    @include('admin.settings.partials.' . $group, ['settings' => $settings, 'getUrl' => $getUrl])

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar
                            Alterações</button>
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
            // Initialize Colorpicker
            $('.colorpicker-element').colorpicker();
            $('.colorpicker-element').on('colorpickerChange', function (event) {
                $(this).find('.fa-square').css('color', event.color.toString());
            });

            // Initialize InputMask
            $('.mask-phone').inputmask('(99) 99999-9999');
            $('.mask-cep').inputmask('99999-999');
        });
    </script>
@endpush
