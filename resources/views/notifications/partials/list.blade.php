@forelse($notifications as $notification)
    @php
        $data = $notification->data;
        $isUnread = is_null($notification->read_at);

        $icon = 'fas fa-bell';
        $bgIcon = 'bg-gray-100';
        $textColor = 'text-gray-600';

        if (str_contains($notification->type, 'Message')) {
            $icon = 'fas fa-comments';
            $bgIcon = 'bg-blue-100';
            $textColor = 'text-blue-600';
        } elseif (str_contains($notification->type, 'Connection')) {
            $icon = 'fas fa-user-plus';
            $bgIcon = 'bg-green-100';
            $textColor = 'text-green-600';
        } elseif (str_contains($notification->type, 'Order') || str_contains($notification->type, 'Sale') || str_contains($notification->type, 'Payment')) {
            $icon = 'fas fa-shopping-cart';
            if (str_contains($notification->type, 'Sale'))
                $icon = 'fas fa-dollar-sign';
            if (str_contains($notification->type, 'Payment'))
                $icon = 'fas fa-check-circle';
            $bgIcon = 'bg-amber-100';
            $textColor = 'text-amber-600';
        } elseif (str_contains($notification->type, 'Event')) {
            $icon = 'fas fa-calendar-alt';
            $bgIcon = 'bg-purple-100';
            $textColor = 'text-purple-600';
        } elseif (str_contains($notification->type, 'Reaction')) {
            $icon = 'fas fa-heart';
            $bgIcon = 'bg-red-100';
            $textColor = 'text-red-600';
        } elseif (str_contains($notification->type, 'Comment') || str_contains($notification->type, 'Reply')) {
            $icon = 'fas fa-comment-alt';
            if (str_contains($notification->type, 'Reply'))
                $icon = 'fas fa-reply';
            $bgIcon = 'bg-indigo-100';
            $textColor = 'text-indigo-600';
        }
    @endphp

    <div class="bg-white border rounded-2xl p-5 shadow-sm transition-all hover:shadow-md hover:border-blue-200 group {{ $isUnread ? 'is-new-notification bg-blue-50/50 border-blue-100' : 'border-gray-100' }}"
        data-notification-id="{{ $notification->id }}" data-read="{{ $isUnread ? 'false' : 'true' }}">

        @if($isUnread)
            <div class="unread-dot"></div>
        @endif

        <div class="flex gap-4">
            <!-- Icon -->
            <div
                class="hidden sm:flex shrink-0 w-12 h-12 {{ $bgIcon }} rounded-xl items-center justify-center transition-colors group-hover:scale-105 transform">
                <i class="{{ $icon }} {{ $textColor }} text-xl"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-gray-900 font-medium leading-relaxed">
                        {{ $data['message'] ?? 'Você tem uma nova notificação' }}
                    </p>
                    <button
                        class="delete-notification shrink-0 text-gray-300 hover:text-red-500 p-1 transition opacity-0 group-hover:opacity-100"
                        data-id="{{ $notification->id }}" title="Remover">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                    <span class="text-xs text-gray-400 flex items-center gap-1">
                        <i class="far fa-clock"></i>
                        {{ $notification->created_at->diffForHumans() }}
                    </span>

                    @if(isset($data['action_url']))
                        <a href="{{ $data['action_url'] }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            {{ $data['action_label'] ?? 'Ver detalhes' }}
                            <i class="fas fa-chevron-right ml-0.5 text-[8px]"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="bg-white border border-gray-100 rounded-3xl p-12 text-center shadow-sm">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">Nenhuma notificação encontrada</h3>
        <p class="text-gray-500 mt-1">Tudo limpo por aqui! Você será avisado quando algo novo acontecer.</p>

        @if(request('filter'))
            <a href="{{ route('notifications.index') }}"
                class="inline-block mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700">
                Ver todas as notificações
            </a>
        @endif
    </div>
@endforelse