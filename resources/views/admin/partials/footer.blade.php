<footer class="app-footer main-footer">
    @php
        $footerSiteName = \App\Models\Setting::get('app_name')
            ?: \App\Models\Setting::get('company_name')
            ?: config('app.name', 'UNN');
    @endphp
    <div class="float-end float-right d-none d-sm-inline">
        <b>Versão</b> {{ config('app.version', '2.5.0') }}
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('home') }}">{{ $footerSiteName }}</a>.</strong> Todos os direitos reservados.
</footer>
