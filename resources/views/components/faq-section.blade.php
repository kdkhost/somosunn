@props([
    'context' => 'general',
    'title' => 'Perguntas Frequentes',
    'perPage' => 4,
    'sectionClass' => 'py-16 px-6 md:px-12 lg:px-24',
    'containerClass' => 'max-w-4xl mx-auto',
])

@php
    $context = (string) $context;
    $perPage = max(1, (int) $perPage);

    $faqs = null;
    $resolvedContext = $context;

    try {
        if (view()->shared('unnDbAvailable') && \Illuminate\Support\Facades\Schema::hasTable('faqs')) {
            $pageName = $context . '_faq_page';

            $faqs = \App\Models\Faq::query()
                ->where('is_active', true)
                ->where('context', $context)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate($perPage, ['*'], $pageName)
                ->withQueryString();

            if ($faqs->total() === 0 && $context !== 'general') {
                $resolvedContext = 'general';
                $pageName = 'general_faq_page';

                $faqs = \App\Models\Faq::query()
                    ->where('is_active', true)
                    ->where('context', 'general')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->paginate($perPage, ['*'], $pageName)
                    ->withQueryString();
            }
        }
    } catch (\Throwable $e) {
        $faqs = null;
    }
@endphp

@if($faqs && $faqs->count())
    @php
        $useAccordion = $faqs->total() > $perPage;
    @endphp
    <section class="{{ $sectionClass }}">
        <div class="{{ $containerClass }}">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">{{ $title }}</h2>

            @if($useAccordion)
                <style>
                    /* accordion (details/summary) */
                    .unn-faq summary { list-style: none; }
                    .unn-faq summary::-webkit-details-marker { display: none; }
                    .unn-faq details[open] .unn-faq-chevron { transform: rotate(180deg); }
                </style>

                <div class="space-y-4 unn-faq">
                    @foreach($faqs as $faq)
                        <details class="bg-white rounded-2xl p-6 shadow-lg">
                            <summary class="cursor-pointer select-none flex items-start justify-between gap-4">
                                <span class="font-bold text-gray-900">{{ $faq->question }}</span>
                                <span class="text-gray-400 pt-1 unn-faq-chevron transition-transform">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </summary>
                            <div class="mt-3 text-gray-600 leading-relaxed">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="space-y-4">
                    @foreach($faqs as $faq)
                        <div class="bg-white rounded-2xl p-6 shadow-lg">
                            <div class="font-bold text-gray-900">{{ $faq->question }}</div>
                            <div class="mt-3 text-gray-600 leading-relaxed">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($faqs->hasPages())
                <div class="mt-8">
                    {{ $faqs->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </section>
@endif
