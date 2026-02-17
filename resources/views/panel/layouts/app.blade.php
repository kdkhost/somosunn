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

    <style>
        /* Summernote Dark Mode Global Overrides */
        .note-editor.note-frame {
            border-radius: 1.25rem !important;
            border-color: #e2e8f0 !important;
            overflow: hidden;
        }

        .note-toolbar {
            background-color: #f8fafc !important;
            border-bottom-color: #e2e8f0 !important;
            padding: 0.75rem !important;
        }

        .dark .note-editor.note-frame {
            border-color: #1e293b !important;
            background-color: #020617 !important;
        }

        .dark .note-toolbar {
            background-color: #0f172a !important;
            border-bottom-color: #1e293b !important;
        }

        .dark .note-btn {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark .note-editable {
            background-color: #020617 !important;
            color: #f8fafc !important;
        }

        .dark .note-statusbar {
            background-color: #0f172a !important;
            border-top-color: #1e293b !important;
        }

        .dark .note-dropdown-menu {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
        }

        .dark .note-dropdown-item {
            color: #f8fafc !important;
        }

        .dark .note-dropdown-item:hover {
            background-color: #1e293b !important;
        }
    </style>
@endpush

@section('content')
    <div class="bg-slate-50 dark:bg-slate-950 min-h-screen pb-10 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16">

            {{-- Breadcrumb (Top Navigation) --}}
            <nav class="h-24 flex items-center justify-between gap-4" aria-label="breadcrumb">
                <ol
                    class="list-none p-0 inline-flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium">
                    <li class="flex items-center">
                        <a href="{{ route('panel.dashboard') }}"
                            class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 shadow-sm">
                                <i class="fas fa-home text-xs"></i>
                            </div>
                            <span>Painel</span>
                        </a>
                    </li>
                    @hasSection('panel_breadcrumb')
                        <li class="flex items-center text-slate-300 dark:text-slate-700">
                            <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                        </li>
                        <li class="flex items-center">
                            @yield('panel_breadcrumb')
                        </li>
                    @endif
                    <li class="flex items-center text-slate-300 dark:text-slate-700">
                        <i class="fas fa-chevron-right text-[10px] mx-1"></i>
                    </li>
                    <li class="flex items-center text-blue-600 dark:text-blue-400">
                        <span
                            class="font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-full text-xs">
                            @yield('title', ucfirst(Str::after(Route::currentRouteName(), 'panel.')))
                        </span>
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col md:flex-row gap-8">
                <aside class="hidden md:block w-72 shrink-0 h-full">
                    @include('panel.partials.sidebar')
                </aside>
                <div class="flex-1 min-w-0">
                    @yield('panel_content')
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