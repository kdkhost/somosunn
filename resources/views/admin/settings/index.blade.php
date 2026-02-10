@extends('admin.layouts.app')

@php
    $groupLabels = [
        'general' => 'Geral',
        'appearance' => 'Aparência',
        'images' => 'Imagens',
        'player' => 'Player',
        'ads' => 'Anúncios',
        'pwa' => 'PWA',
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

        .upload-box {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .upload-box:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .upload-preview img {
            max-width: 100%;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

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

            // Upload Box Logic
            $('.upload-btn').click(function () {
                $(this).closest('.upload-box').find('input[type=file]').click();
            });

            $('.upload-box input[type=file]').change(function () {
                var file = this.files[0];
                var box = $(this).closest('.upload-box');
                var preview = box.find('.upload-preview');
                var removeBtn = box.find('.upload-remove');
                var removeInput = box.data('remove-input');

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        preview.html('<img src="' + e.target.result + '">');
                        removeBtn.removeClass('d-none');
                        if (removeInput) $(removeInput).val('0');
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('.upload-remove').click(function () {
                var box = $(this).closest('.upload-box');
                var input = box.find('input[type=file]');
                var preview = box.find('.upload-preview');
                var removeInput = box.data('remove-input');

                input.val('');
                preview.html('');
                $(this).addClass('d-none');
                if (removeInput) $(removeInput).val('1');
            });

            // Initialize existing images
            $('.upload-box').each(function () {
                var url = $(this).data('existing-url');
                if (url) {
                    $(this).find('.upload-preview').html('<img src="' + url + '">');
                    $(this).find('.upload-remove').removeClass('d-none');
                }
            });
        });
    </script>
@endpush