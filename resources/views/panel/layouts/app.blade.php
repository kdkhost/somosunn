@extends('layouts.app')

@section('content')

    <div class="bg-slate-50 min-h-screen pt-20 pb-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-8 lg:px-16">
            <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                <aside class="w-full lg:w-80 mb-4 lg:mb-0">
                    @include('panel.partials.sidebar')
                </aside>
                <main class="flex-1 min-w-0">
                    @yield('panel_content')
                </main>
            </div>
        </div>
    </div>

@push('scripts')
    <!-- Shepherd.js (tour guiado) -->
    <link rel="stylesheet" href="/vendor/shepherd/css/shepherd.css">
    <script src="/vendor/shepherd/js/shepherd.min.js"></script>
    <script src="/js/member-tour.js"></script>
@endpush
@endsection

