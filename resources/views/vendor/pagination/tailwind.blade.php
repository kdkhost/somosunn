@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação de Páginas" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 w-full">
        <div class="text-xs sm:text-sm text-slate-500 font-medium">
            @if ($paginator->firstItem())
                Exibindo <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span> a <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span> de <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> resultados
            @else
                Total de <span class="font-bold text-slate-800">{{ $paginator->count() }}</span> resultados
            @endif
        </div>

        <div>
            <div class="inline-flex items-center gap-1.5 p-1 bg-white border border-slate-200/90 rounded-full shadow-sm select-none">
                {{-- Botão Anterior --}}
                @if ($paginator->onFirstPage())
                    <span class="w-10 h-10 flex items-center justify-center rounded-full text-slate-300 cursor-not-allowed text-xs font-bold" aria-disabled="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors text-xs font-bold" aria-label="Página anterior">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                {{-- Números de Página --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="w-10 h-10 flex items-center justify-center text-slate-400 font-bold text-xs" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="w-10 h-10 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-black text-xs shadow-md shadow-blue-500/25">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors font-bold text-xs" aria-label="Ir para a página {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Botão Próximo --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors text-xs font-bold" aria-label="Próxima página">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="w-10 h-10 flex items-center justify-center rounded-full text-slate-300 cursor-not-allowed text-xs font-bold" aria-disabled="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
