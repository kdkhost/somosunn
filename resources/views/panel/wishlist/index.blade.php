@extends('panel.layouts.app')

@section('title', 'Minha Lista - UNN')

@section('panel_content')
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Minha Lista de Desejos</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">
                    Cursos que você salvou para ver depois.
                </p>
            </div>
            <a href="{{ route('courses.index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="fas fa-plus mr-2"></i> Adicionar Cursos
            </a>
        </div>
    </div>

    @if($courses->isEmpty())
        <div
            class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
            <div
                class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 mb-6">
                <i class="far fa-heart text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Sua lista está vazia</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-md mx-auto">
                Você ainda não adicionou nenhum curso à sua lista de desejos. Explore a biblioteca e clique no coração para
                salvar.
            </p>
            <a href="{{ route('courses.index') }}"
                class="mt-8 inline-flex items-center justify-center px-8 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                Explorar Biblioteca
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($courses as $course)
                @php
                    $thumbUrl = $course->thumbnail ? asset('storage/' . $course->thumbnail) : null;
                    $price = (float) $course->price;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md transition group relative">
                    <div class="relative h-48 bg-slate-100 dark:bg-slate-800">
                        @if($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                        @endif

                        <button onclick="toggleWishlist({{ $course->id }}, this)"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-red-500 flex items-center justify-center shadow-sm hover:scale-110 transition"
                            title="Remover da lista">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <div class="p-5">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white line-clamp-1 mb-2">
                            {{ $course->title }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-4 h-10">
                            {{ $course->short_description ?? 'Sem descrição.' }}
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                {{ $price > 0 ? 'R$ ' . number_format($price, 2, ',', '.') : 'Gratuito' }}
                            </span>
                            <a href="{{ route('checkout.show', $course) }}"
                                class="text-sm font-bold text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                                Comprar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    @endif

    @push('scripts')
        <script>
            async function toggleWishlist(courseId, btn) {
                if (!confirm('Remover este curso da sua lista?')) return;

                try {
                    const response = await fetch(`{{ url('/painel/minha-lista/toggle') }}/${courseId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (data.success && !data.is_wishlisted) {
                        // Remove card visually
                        const card = btn.closest('.group');
                        card.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            card.remove();
                            // Reload if empty to show empty state
                            if (document.querySelectorAll('.group').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                } catch (err) {
                    console.error('Erro ao remover', err);
                    alert('Erro ao processar solicitação.');
                }
            }
        </script>
    @endpush
@endsection