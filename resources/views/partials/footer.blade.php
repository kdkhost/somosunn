{{--
* Sistema UNN - Rodapé
* Autor: George Marcelo (KDKHOST SOLUÇÕES)
* Telefone: +55 (21) 98132-5441
* Telegram: https://t.me/MARCELO_BRAD
* Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
* AVISO LEGAL:
* Este software e seu código-fonte são propriedade intelectual de KDKHOST Soluções.
* É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
* total ou parcial, sem autorização prévia e por escrito.
* Contato: contato@kdkhost.com.br
* Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
--}}
@php
    $footerText = trim((string) \App\Models\Setting::get('footer_text'));
@endphp
<footer class="bg-white border-t border-gray-100 mt-auto">
    <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-2 items-center text-gray-500">
        <div
            class="text-xs sm:text-sm text-center sm:text-left flex items-center justify-center sm:justify-start gap-4">
            <span>{{ $footerText !== '' ? $footerText : '© ' . date('Y') . ' UNN.' }}</span>
            <a href="{{ route('jobs.public.index') }}" class="hover:text-[#1F5EDB] transition-colors font-medium">Vagas
                Abertas</a>
        </div>
        <div class="text-xs text-center sm:text-right hidden md:block">
            Desenvolvido por:
            <a href="https://kdkhost.com.br" target="_blank" rel="noopener" class="font-semibold hover:underline"
                style="color: var(--unn-azul-1)">
                Marcelo Brad RJ
            </a>
            · site kdkhost.com.br
        </div>
    </div>
</footer>