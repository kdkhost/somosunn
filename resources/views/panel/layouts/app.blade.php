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

            {{-- Breadcrumb (Top Navigation) --}}
            <nav class="mb-6 flex flex-wrap items-center justify-between gap-4" aria-label="breadcrumb">
                <ol class="list-none p-0 inline-flex flex-wrap items-center gap-2 text-sm text-slate-500 font-medium">
                    <li class="flex items-center">
                        <a href="{{ route('panel.dashboard') }}"
                            class="hover:text-blue-600 transition-colors flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                <i class="fas fa-home text-xs"></i>
                            </div>
                            <span>Painel</span>
                        </a>
                    </li>
                    @hasSection('panel_breadcrumb')
                        <li class="flex items-center text-slate-300">
                            <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                        </li>
                        <li class="flex items-center">
                            @yield('panel_breadcrumb')
                        </li>
                    @endif
                    <li class="flex items-center text-slate-300">
                        <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                    </li>
                    <li class="flex items-center text-blue-600">
                        <span class="font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs">
                            @yield('title', ucfirst(Str::after(Route::currentRouteName(), 'panel.')))
                        </span>
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col lg:flex-row gap-8">
                <aside class="w-full lg:w-72 shrink-0">
                    @include('panel.partials.sidebar')
                </aside>
                <main class="flex-1 min-w-0">
                    @yield('panel_content')
                </main>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        <!-- FilePond JS -->
        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
        <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

        <!-- Tagify JS -->
        <script src="https://unpkg.com/@yaireo/tagify"></script>
    @endpush
@endsection