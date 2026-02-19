@push('styles')
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 24px;
            line-height: 1;
            margin: 0;
            transition: color 0.15s ease;
        }

        .unn-star-rating input:checked~label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover~label {
            color: #f59e0b;
        }
    </style>
@endpush

@auth
    @php
        $selectedRating = old('rating');
        $selectedRating = is_numeric($selectedRating) ? max(1, min(5, (int) $selectedRating)) : null;
    @endphp



    @if($myReview)
        <div
            class="mb-4 rounded-lg px-4 py-3 border {{ $myReview->status === 'approved' ? 'bg-green-50 border-green-200 text-green-700' : ($myReview->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-yellow-50 border-yellow-200 text-yellow-700') }}">
            @if($myReview->status === 'approved')
                Sua avaliação está publicada.
            @elseif($myReview->status === 'rejected')
                Sua avaliação foi recusada. Você pode ajustar e enviar novamente.
            @else
                Sua avaliação está em moderação.
            @endif
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="border border-slate-200 rounded-xl p-5">
        @csrf
        <h3 class="text-lg font-bold text-gray-900 mb-1">Envie sua avaliação</h3>
        <p class="text-sm text-gray-500 mb-4">Sua avaliação será moderada antes de aparecer na página.</p>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-800 mb-2">Nota</label>
            <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="review-rating-{{ $i }}" name="rating" value="{{ $i }}" {{ (string) $selectedRating === (string) $i ? 'checked' : '' }}>
                    <label for="review-rating-{{ $i }}" title="{{ $i }} de 5"><i class="fas fa-star"></i></label>
                @endfor
            </div>
            @error('rating')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="review-comment" class="block text-sm font-semibold text-gray-800 mb-2">Comentário</label>
            <textarea id="review-comment" name="comment" rows="4"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Conte como foi sua experiência...">{{ old('comment') }}</textarea>
            @error('comment')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#1F5EDB] hover:bg-blue-700 text-white font-semibold px-5 py-2.5 transition">
            <i class="fas fa-paper-plane"></i>
            {{ $myReview ? 'Atualizar avaliação' : 'Enviar avaliação' }}
        </button>
    </form>
@else
    <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-600">
        Faça <a href="{{ route('login') }}" class="text-blue-600 font-semibold">login</a> para enviar uma avaliação.
    </div>
@endauth