<footer class="main-footer">
    @php
        $footerSiteName = \App\Models\Setting::get('app_name')
            ?: \App\Models\Setting::get('company_name')
            ?: config('app.name', 'UNN');
    @endphp
    <div class="float-right d-none d-sm-block">
        <b>Versão</b> 1.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ $footerSiteName }}</a>.</strong> Todos os direitos reservados.
</footer>
