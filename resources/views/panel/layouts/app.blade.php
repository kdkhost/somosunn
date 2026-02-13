@extends('layouts.app')

@section('content')
    <div class="bg-slate-50 min-h-screen pt-24 pb-10">
        <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-80">
                    @include('panel.partials.sidebar')
                </aside>
                <main class="flex-1">
                    @yield('panel_content')
                </main>
            </div>
        </div>
    </div>

@push('scripts')
    <!-- Shepherd.js (tour guiado) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@9.1.2/dist/css/shepherd.css">
    <script src="https://cdn.jsdelivr.net/npm/shepherd.js@9.1.2/dist/js/shepherd.min.js"></script>
    <script src="/js/member-tour.js"></script>
@endpush
@endsection

