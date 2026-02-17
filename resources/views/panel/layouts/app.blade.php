@extends('layouts.app')

@push('styles')
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.css"
        rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.css"
        rel="stylesheet">

    <!-- Tagify CSS -->
    <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="bg-slate-50 min-h-screen pt-24 pb-10">
        <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-80">
                    @include('panel.partials.sidebar')
                </aside>
                <main class="flex-1">
                    <nav class="text-sm text-slate-500 mb-4" aria-label="breadcrumb">
                        <ol class="list-none p-0 inline-flex gap-1">
                            <li class="flex items-center">
                                <i class="fas fa-home mr-1"></i>
                                <a href="{{ route('panel.dashboard') }}" class="hover:underline">Painel</a>
                            </li>
                            @hasSection('panel_breadcrumb')
                                <li class="flex items-center">
                                    <span class="mx-2">/</span>
                                    @yield('panel_breadcrumb')
                                </li>
                            @endif
                            <li class="flex items-center">
                                <span class="mx-2">/</span>
                                <span
                                    class="font-bold text-slate-700">@yield('title', ucfirst(Str::after(Route::currentRouteName(), 'panel.')))</span>
                            </li>
                        </ol>
                    </nav>
                    @yield('panel_content')
                </main>
            </div>
        </div>
    </div>
@endsection