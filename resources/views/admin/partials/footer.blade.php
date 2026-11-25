<footer class="app-footer">
    @php
        $footerSiteName = \App\Models\Setting::get('app_name')
            ?: \App\Models\Setting::get('company_name')
            ?: config('app.name', 'UNN');
    @endphp
    <div class="float-end d-none d-sm-inline">
        <strong>Versão</strong> {{ config('app.version', '2.8.38') }}
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">{{ $footerSiteName }}</a>.</strong>
    Todos os direitos reservados.
</footer>
