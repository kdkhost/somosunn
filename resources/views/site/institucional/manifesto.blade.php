@extends('layouts.app')

@section('title', $page->get('seo_title', 'Manifesto UNN - Nossa Visão'))

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-16 md:pt-24 pb-12 overflow-x-hidden">
        <div class="unn-container">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black leading-[1.1] tracking-tight mb-6 unn-title-gradient">
                    {{ $page->get('hero_title', 'Acreditamos no poder das conexões humanas.') }}
                </h1>
                <p class="text-lg md:text-xl text-slate-500 leading-relaxed font-medium">
                    {{ $page->get('hero_subtitle', 'Esse é o nosso manifesto. Leia com o coração.') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Manifesto Content -->
    <section class="pb-16 overflow-x-hidden">
        <div class="unn-container px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-[2.5rem] shadow-[0_40px_100px_-20px_rgba(15,23,42,0.1)] p-8 md:p-16 border border-slate-100">
                    <article class="prose prose-lg md:prose-xl max-w-none prose-headings:font-black prose-headings:tracking-tight prose-p:text-slate-600 prose-p:leading-relaxed">
                        @if($page->get('body'))
                            {!! $page->get('body') !!}
                        @else
                            <div class="space-y-12">
                                <div class="relative">
                                    <span class="absolute -left-4 md:-left-8 top-0 text-6xl text-blue-100 font-serif opacity-50">"</span>
                                    <p class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">
                                        {{ $page->get('quote_top', 'Nenhum empreendedor chega longe sozinho. Por trás de todo grande negócio, existe uma rede de pessoas que acreditaram, apoiaram e abriram portas.') }}
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    <h2 class="text-2xl font-black text-slate-900">{{ $page->get('section_1_title', 'Acreditamos que o networking é uma habilidade.') }}</h2>
                                    <p>
                                        {{ $page->get('section_1_text', 'Não um talento inato ou um dom de poucos. É algo que pode ser desenvolvido, praticado e aperfeiçoado. E quando bem feito, transforma trajetórias inteiras.') }}
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    <h2 class="text-2xl font-black text-slate-900">{{ $page->get('section_2_title', 'Acreditamos na generosidade estratégica.') }}</h2>
                                    <p>
                                        {{ $page->get('section_2_text', 'A melhor conexão começa quando você pergunta "como posso ajudar?" antes de "o que você pode fazer por mim?". Dar sem esperar retorno imediato é o investimento mais inteligente que um empreendedor pode fazer.') }}
                                    </p>
                                </div>

                                <div class="mt-16 p-10 rounded-[2rem] text-center relative overflow-hidden group" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%)">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <p class="text-xl md:text-2xl font-black text-slate-900 mb-2 relative z-10">
                                        "{{ $page->get('quote_bottom', 'Sozinhos vamos mais rápido. Juntos vamos mais longe.') }}"
                                    </p>
                                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] relative z-10">{{ $page->get('quote_author', 'Filosofia UNN') }}</p>
                                </div>
                            </div>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Preview -->
    <section class="py-24 bg-white border-y border-slate-100">
        <div class="unn-container">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-6">{{ $page->get('pillars_title', 'Nossos Pilares') }}</h2>
                <div class="w-20 h-1.5 bg-blue-600 mx-auto rounded-full"></div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <div class="p-8 rounded-[2rem] bg-slate-50 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-lg">{{ $page->get('pillar_1_title', 'Confiança') }}</h3>
                </div>
                <div class="p-8 rounded-[2rem] bg-slate-50 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-hands-helping text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-lg">{{ $page->get('pillar_2_title', 'Generosidade') }}</h3>
                </div>
                <div class="p-8 rounded-[2rem] bg-slate-50 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-lightbulb text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-lg">{{ $page->get('pillar_3_title', 'Inovação') }}</h3>
                </div>
                <div class="p-8 rounded-[2rem] bg-slate-50 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-lg">{{ $page->get('pillar_4_title', 'Excelência') }}</h3>
                </div>
            </div>
            <div class="text-center mt-12">
                <a href="{{ route('valores') }}" class="btn-primary text-white px-10 py-4 rounded-xl font-black inline-flex items-center gap-3 transition-all hover:scale-105 shadow-lg shadow-blue-500/20">
                    {{ $page->get('pillars_link_text', 'Conhecer nossos valores') }} <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-24 overflow-x-hidden">
        <div class="unn-container">
            <div class="rounded-[3rem] p-10 md:p-16 text-center text-white relative overflow-hidden" style="background: linear-gradient(135deg, #1A237E 0%, #1F5EDB 50%, #00B0FF 100%);">
                <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4v-4H4v4H0v2h4v4h2v-4h4v-2H6zm30 0v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h2 class="text-3xl lg:text-5xl font-black mb-6 leading-tight">{{ $page->get('cta_title', 'Se identificou com nossa visão?') }}</h2>
                    <p class="text-lg md:text-xl opacity-80 mb-10 font-medium">{{ $page->get('cta_subtitle', 'Faça parte de uma comunidade que pensa como você.') }}</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-3 bg-white px-10 py-5 rounded-xl font-black transition-all hover:scale-105 hover:shadow-2xl group" style="color: var(--unn-azul-1)">
                        <i class="fas fa-rocket group-hover:rotate-12 transition-transform"></i>
                        {{ $page->get('cta_btn', 'Quero fazer parte') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #1A237E 0%, #1F5EDB 50%, #00B0FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
    </style>
@endsection
