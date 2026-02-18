<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    @forelse($reviews as $review)
        <article class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0 overflow-hidden">
                        @if(!empty($review->user->photo))
                            <img src="{{ $review->user->profile_photo_url ?? '' }}"
                                alt="Foto de {{ $review->user->name ?? 'Usuário' }}"
                                class="w-full h-full object-cover rounded-full">
                        @else
                            {{ strtoupper(mb_substr((string) ($review->user->name ?? 'U'), 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">{{ $review->user->name ?? 'Usuário' }}</div>
                        <div class="text-xs text-gray-500">{{ optional($review->created_at)->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="text-amber-500 text-sm whitespace-nowrap">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                    @endfor
                </div>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($review->comment)) !!}</p>
        </article>
    @empty
        <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-slate-500">
            {{ $emptyMessage ?? 'Ainda não há avaliações aprovadas.' }}
        </div>
    @endforelse
</div>