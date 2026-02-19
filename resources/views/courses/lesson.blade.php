@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)
@php
    $formatSeconds = static function ($seconds): string {
        $seconds = max(0, (int) $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $m, $s);
    };
@endphp

@section('content')
    <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
        <!-- Sidebar - Playlist -->
        <div
            class="w-full lg:w-80 bg-white border-r border-gray-200 flex-shrink-0 h-auto lg:h-screen lg:sticky lg:top-0 overflow-y-auto z-10">
            @php
                $lessons = $course->lessons()->orderBy('order')->get();
                $totalLessons = $lessons->count();
                $completedLessonsCount = 0;
                $lessonProgressMap = [];

                if (Auth::check()) {
                    $completedLessonsCount = \App\Models\LessonProgress::where('user_id', Auth::id())
                        ->whereIn('lesson_id', $lessons->pluck('id'))
                        ->whereNotNull('completed_at')
                        ->count();

                    $lessonProgressMap = \App\Models\LessonProgress::where('user_id', Auth::id())
                        ->whereIn('lesson_id', $lessons->pluck('id'))
                        ->pluck('completed_at', 'lesson_id')
                        ->toArray();
                }

                $percentage = $totalLessons > 0 ? round(($completedLessonsCount / $totalLessons) * 100) : 0;
            @endphp
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800 text-lg leading-tight">{{ $course->title }}</h2>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                        style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% Concluído</p>
            </div>
            <div class="py-2">
                @foreach($course->lessons()->orderBy('order')->get() as $l)
                    <a href="{{ route('courses.lessons.show', [$course->id, $l->id]) }}"
                        class="flex items-center p-4 hover:bg-gray-50 transition border-l-4 {{ $l->id == $lesson->id ? 'border-[#1F5EDB] bg-blue-50' : 'border-transparent' }}">
                        <div class="mr-3">
                            @if(isset($lessonProgressMap[$l->id]) && $lessonProgressMap[$l->id])
                                <i class="fas fa-check-circle text-green-500"></i>
                            @elseif($l->id == $lesson->id)
                                <i class="fas fa-play text-[#1F5EDB]"></i>
                            @else
                                <i class="far fa-circle text-gray-400"></i>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium {{ $l->id == $lesson->id ? 'text-[#1F5EDB]' : 'text-gray-700' }}">
                                {{ $l->order }}. {{ $l->title }}
                            </p>
                            @if($l->duration > 0)
                                <p class="text-xs text-gray-400 mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ gmdate("H:i", $l->duration) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Main Content - Player -->
        <div class="flex-1 p-6 md:p-10 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $lesson->title }}</h1>

                <div class="relative w-full overflow-hidden shadow-2xl mb-8 bg-black rounded-xl" style="aspect-ratio: 16/9;"
                    data-unn-video-host>
                    @if($lesson->video_url)
                        @php
                            $normalizedVideoUrl = trim((string) $lesson->video_url);
                            if (
                                $normalizedVideoUrl !== '' &&
                                !preg_match('/^(https?:)?\\/\\//i', $normalizedVideoUrl)
                            ) {
                                $candidate = ltrim(str_replace('\\', '/', $normalizedVideoUrl), '/');
                                if (\Illuminate\Support\Str::startsWith($candidate, 'storage/app/public/')) {
                                    $candidate = 'storage/' . substr($candidate, strlen('storage/app/public/'));
                                } elseif (\Illuminate\Support\Str::startsWith($candidate, 'public/')) {
                                    $candidate = substr($candidate, strlen('public/'));
                                }

                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($candidate)) {
                                    $normalizedVideoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($candidate);
                                } elseif (\Illuminate\Support\Str::startsWith($candidate, 'storage/')) {
                                    $normalizedVideoUrl = '/' . $candidate;
                                } else {
                                    $normalizedVideoUrl = '/' . $candidate;
                                }
                            }
                        @endphp
                        @php
                            $usePlyr = (string) \App\Models\Setting::get('video_player_enabled', '1') === '1';

                            // Watermark Settings
                            $wmEnabled = (bool) \App\Models\Setting::get('video_watermark_enabled', '0');
                            $wmImage = \App\Models\Setting::get('video_watermark_image');
                            if ($wmImage) {
                                if (\Illuminate\Support\Str::startsWith($wmImage, 'storage/')) {
                                    $wmImage = asset($wmImage);
                                } elseif (\Illuminate\Support\Str::startsWith($wmImage, 'uploads/')) {
                                    $wmImage = asset($wmImage);
                                } else {
                                    $wmImage = asset('storage/' . $wmImage);
                                }
                            }

                            $wmTextEnabled = (bool) \App\Models\Setting::get('video_watermark_text_enabled', '0');
                            $wmTextTemplate = \App\Models\Setting::get('video_watermark_text_template', '{name} - {email}');
                            $wmOpacity = (float) \App\Models\Setting::get('video_watermark_opacity', '0.5');
                            $wmPosition = \App\Models\Setting::get('video_watermark_position', 'top-right');

                            // User Info for Text
                            $currentUser = Auth::user();
                            $wmText = '';
                            if ($currentUser) {
                                $wmText = str_replace(
                                    ['{name}', '{email}', '{cpf}', '{id}'],
                                    [$currentUser->name, $currentUser->email, $currentUser->cpf ?? '', $currentUser->id],
                                    $wmTextTemplate
                                );
                            }
                        @endphp
                        @if($usePlyr)
                            <div class="absolute inset-0" data-unn-video-player data-video-url="{{ $normalizedVideoUrl }}"
                                data-progress-url="{{ route('courses.lessons.progress.update', [$course->id, $lesson->id]) }}"
                                data-bookmark-store-url="{{ route('courses.lessons.bookmarks.store', [$course->id, $lesson->id]) }}"
                                data-bookmark-delete-url-template="{{ route('courses.lessons.bookmarks.destroy', [$course->id, $lesson->id, '__BOOKMARK_ID__']) }}"
                                data-resume-at="{{ (int) ($resumeAt ?? 0) }}"
                                data-block-download="{{ !empty($course->video_block_download) ? '1' : '0' }}"
                                data-floating-enabled="{{ !empty($course->video_floating_enabled) ? '1' : '0' }}"
                                data-floating-width="{{ (int) ($course->video_floating_width ?? 420) }}"
                                data-floating-height="{{ (int) ($course->video_floating_height ?? 236) }}"
                                data-wm-enabled="{{ $wmEnabled ? '1' : '0' }}" data-wm-image="{{ $wmImage }}"
                                data-wm-text-enabled="{{ $wmTextEnabled ? '1' : '0' }}" data-wm-text="{{ $wmText }}"
                                data-wm-opacity="{{ $wmOpacity }}" data-wm-position="{{ $wmPosition }}">
                                <video class="w-full h-full object-contain" controls playsinline preload="metadata">
                                    <source src="{{ $normalizedVideoUrl }}">
                                </video>
                            </div>
                        @else
                            <iframe src="{{ str_replace('youtu.be/', 'youtube.com/embed/', $normalizedVideoUrl) }}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen class="absolute inset-0 w-full h-full"></iframe>
                        @endif
                    @else
                        <div class="flex items-center justify-center h-full text-white">
                            <p>Nenhum vídeo disponível para esta aula.</p>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-bold mb-4">Conteúdo da Aula</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! \App\Support\RichText::toHtml($lesson->content) !!}
                    </div>

                    @auth
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <h4 class="text-md font-bold mb-3 flex items-center text-gray-800">
                                <i class="fas fa-bookmark mr-2 text-blue-500"></i> Anotações da aula
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">Marque o momento do vídeo e escreva um comentário para revisar
                                depois ou tirar dúvidas com o instrutor.</p>

                            <div class="flex flex-col md:flex-row gap-3 mb-4">
                                <button type="button" id="lessonBookmarkCurrentTime"
                                    class="px-3 py-2 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 text-sm font-semibold md:w-32 text-center">
                                    00:00
                                </button>
                                <input type="text" id="lessonBookmarkNote" maxlength="1000"
                                    placeholder="Ex: Rever este ponto com o instrutor..."
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <button type="button" id="lessonBookmarkAddBtn"
                                    class="px-4 py-2 rounded-lg bg-[#1F5EDB] text-white hover:bg-blue-700 transition text-sm font-semibold">
                                    Salvar marcador
                                </button>
                            </div>

                            <div id="lessonBookmarkList" class="space-y-2">
                                @forelse($bookmarks as $bookmark)
                                    <div class="flex items-start justify-between gap-3 p-3 border border-gray-200 rounded-lg"
                                        data-bookmark-id="{{ $bookmark->id }}"
                                        data-bookmark-seconds="{{ (int) $bookmark->position_seconds }}">
                                        <div class="min-w-0">
                                            <button type="button"
                                                class="text-sm font-bold text-[#1F5EDB] hover:underline text-left lesson-bookmark-jump">
                                                <i
                                                    class="fas fa-play-circle mr-1"></i>{{ $formatSeconds($bookmark->position_seconds) }}
                                            </button>
                                            <p class="text-sm text-gray-700 mt-1 break-words">{{ $bookmark->note }}</p>
                                        </div>
                                        <button type="button"
                                            class="text-xs px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50 lesson-bookmark-delete">Excluir</button>
                                    </div>
                                @empty
                                    <p id="lessonBookmarkEmpty" class="text-sm text-gray-500">Nenhum marcador salvo nesta aula.</p>
                                @endforelse
                            </div>
                        </div>
                    @endauth

                    @if($lesson->attachments->count() > 0)
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <h4 class="text-md font-bold mb-3 flex items-center text-gray-800">
                                <i class="fas fa-paperclip mr-2 text-gray-500"></i> Materiais de Apoio
                            </h4>
                            <div class="grid gap-3">
                                @foreach($lesson->attachments as $attachment)
                                    <a href="{{ route('courses.lessons.attachments.download', [$course->id, $lesson->id, $attachment->id]) }}"
                                        download="{{ $attachment->file_name }}"
                                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition group">
                                        <div class="flex items-center overflow-hidden">
                                            <div
                                                class="bg-blue-100 text-blue-600 w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 mr-3">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div class="truncate">
                                                <p
                                                    class="text-sm font-medium text-gray-900 truncate group-hover:text-blue-600 transition">
                                                    {{ $attachment->file_name }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ round($attachment->file_size / 1024 / 1024, 2) }} MB
                                                </p>
                                            </div>
                                        </div>
                                        <i class="fas fa-download text-gray-400 group-hover:text-blue-600 transition"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between items-center">
                    @if($previous)
                        <a href="{{ route('courses.lessons.show', [$course->id, $previous->id]) }}"
                            class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-medium transition">
                            <i class="fas fa-arrow-left mr-2"></i> Anterior
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if($next)
                        <a href="{{ route('courses.lessons.show', [$course->id, $next->id]) }}"
                            class="px-5 py-2 bg-[#1F5EDB] text-white rounded-lg hover:bg-blue-700 font-medium transition">
                            Próxima <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    @else
                        @php
                            $userCertificate = Auth::check() ? $course->certificates()->where('user_id', Auth::id())->first() : null;
                        @endphp

                        @if($userCertificate)
                            <a href="{{ route('admin.certificates.view', $userCertificate->cert_hash) }}" target="_blank"
                                class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition cursor-pointer">
                                Download Certificado <i class="fas fa-file-download ml-2"></i>
                            </a>
                        @else
                            @if($percentage >= 89)
                                <form action="{{ route('courses.complete', $course->id) }}" method="POST" class="d-inline"
                                    id="form-complete-course">
                                    @csrf
                                    <button type="button" id="btn-complete-course"
                                        class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition cursor-pointer">
                                        Concluir Curso <i class="fas fa-check ml-2"></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled title="Conclua pelo menos 89% do curso"
                                    class="px-5 py-2 bg-gray-400 text-white rounded-lg font-medium cursor-not-allowed opacity-70">
                                    Concluir Curso <i class="fas fa-lock ml-2"></i>
                                </button>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @auth
        <script>
            (function () {
                const wrapper = document.querySelector('[data-unn-video-player]');
                if (!wrapper) return;

                const progressUrl = wrapper.dataset.progressUrl || '';
                const bookmarkStoreUrl = wrapper.dataset.bookmarkStoreUrl || '';
                const bookmarkDeleteTemplate = wrapper.dataset.bookmarkDeleteUrlTemplate || '';
                const resumeAt = Number.parseInt(wrapper.dataset.resumeAt || '0', 10) || 0;
                const csrfToken = '{{ csrf_token() }}';

                const bookmarkList = document.getElementById('lessonBookmarkList');
                const bookmarkEmpty = document.getElementById('lessonBookmarkEmpty');
                const bookmarkNote = document.getElementById('lessonBookmarkNote');
                const bookmarkAddBtn = document.getElementById('lessonBookmarkAddBtn');
                const bookmarkCurrentTime = document.getElementById('lessonBookmarkCurrentTime');

                let media = null;
                let lastSavedSeconds = -1;
                let saveTimer = null;
                let resumeApplied = false;

                const formatSeconds = (value) => {
                    const total = Math.max(0, Math.floor(Number(value) || 0));
                    const h = Math.floor(total / 3600);
                    const m = Math.floor((total % 3600) / 60);
                    const s = total % 60;
                    if (h > 0) return [h, m, s].map((v) => String(v).padStart(2, '0')).join(':');
                    return [m, s].map((v) => String(v).padStart(2, '0')).join(':');
                };

                const renderEmptyState = () => {
                    if (!bookmarkList) return;
                    const hasItems = bookmarkList.querySelector('[data-bookmark-id]') !== null;
                    if (bookmarkEmpty) {
                        bookmarkEmpty.style.display = hasItems ? 'none' : '';
                    } else if (!hasItems) {
                        const p = document.createElement('p');
                        p.id = 'lessonBookmarkEmpty';
                        p.className = 'text-sm text-gray-500';
                        p.textContent = 'Nenhum marcador salvo nesta aula.';
                        bookmarkList.appendChild(p);
                    }
                };

                const postJson = async (url, payload, keepalive = false) => {
                    const response = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        keepalive: !!keepalive,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload || {}),
                    });

                    const contentType = String(response.headers.get('content-type') || '').toLowerCase();
                    const data = contentType.includes('application/json')
                        ? await response.json()
                        : null;

                    if (!response.ok) {
                        const message = data && data.message ? String(data.message) : 'Erro na requisição';
                        throw new Error(message);
                    }

                    return data || {};
                };

                const saveProgress = async (force = false, keepalive = false) => {
                    if (!media || !progressUrl) return;
                    const seconds = Math.max(0, Math.floor(Number(media.currentTime) || 0));
                    if (!force && seconds === lastSavedSeconds) return;
                    lastSavedSeconds = seconds;

                    try {
                        await postJson(progressUrl, { current_time_seconds: seconds }, keepalive);
                    } catch (e) {
                        // no-op
                    }
                };

                const onTimeTick = () => {
                    if (!media) return;
                    if (bookmarkCurrentTime) {
                        bookmarkCurrentTime.textContent = formatSeconds(media.currentTime);
                    }
                };

                const bindBookmarkActions = () => {
                    if (!bookmarkList || !media) return;

                    bookmarkList.addEventListener('click', async (event) => {
                        const jumpBtn = event.target.closest('.lesson-bookmark-jump');
                        if (jumpBtn) {
                            const row = jumpBtn.closest('[data-bookmark-id]');
                            if (!row) return;
                            const seconds = Number.parseInt(row.dataset.bookmarkSeconds || '0', 10) || 0;
                            media.currentTime = seconds;
                            if (typeof media.play === 'function') {
                                media.play().catch(() => { });
                            }
                            return;
                        }

                        const deleteBtn = event.target.closest('.lesson-bookmark-delete');
                        if (!deleteBtn) return;

                        const row = deleteBtn.closest('[data-bookmark-id]');
                        if (!row) return;
                        const id = row.dataset.bookmarkId;
                        if (!id || !bookmarkDeleteTemplate) return;
                        const { isConfirmed } = await Swal.fire({
                            title: 'Excluir marcador?',
                            text: "Esta ação não pode ser desfeita.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Sim, excluir!',
                            cancelButtonText: 'Cancelar'
                        });
                        if (!isConfirmed) return;

                        try {
                            const response = await fetch(bookmarkDeleteTemplate.replace('__BOOKMARK_ID__', id), {
                                method: 'DELETE',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                            });
                            if (!response.ok) throw new Error('Falha');
                            row.remove();
                            renderEmptyState();
                        } catch (e) {
                            Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível excluir o marcador.' });
                        }
                    });

                    if (bookmarkAddBtn && bookmarkNote) {
                        bookmarkAddBtn.addEventListener('click', async () => {
                            if (!bookmarkStoreUrl) {
                                Swal.fire({ icon: 'error', title: 'Erro', text: 'Rota de marcador não configurada.' });
                                return;
                            }

                            const note = (bookmarkNote.value || '').trim();
                            if (note.length < 2) {
                                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Digite um comentário com pelo menos 2 caracteres.' });
                                bookmarkNote.focus();
                                return;
                            }

                            const seconds = Math.max(0, Math.floor(Number(media.currentTime) || 0));

                            try {
                                bookmarkAddBtn.disabled = true;
                                const result = await postJson(bookmarkStoreUrl, {
                                    position_seconds: seconds,
                                    note: note,
                                });

                                if (!result || !result.success || !result.bookmark) {
                                    throw new Error('Resposta invalida');
                                }

                                const row = document.createElement('div');
                                row.className = 'flex items-start justify-between gap-3 p-3 border border-gray-200 rounded-lg';
                                row.dataset.bookmarkId = String(result.bookmark.id);
                                row.dataset.bookmarkSeconds = String(result.bookmark.position_seconds || 0);
                                row.innerHTML = `
                                                                                    <div class="min-w-0">
                                                                                        <button type="button" class="text-sm font-bold text-[#1F5EDB] hover:underline text-left lesson-bookmark-jump">
                                                                                            <i class="fas fa-play-circle mr-1"></i>${formatSeconds(result.bookmark.position_seconds || 0)}
                                                                                        </button>
                                                                                        <p class="text-sm text-gray-700 mt-1 break-words"></p>
                                                                                    </div>
                                                                                    <button type="button" class="text-xs px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50 lesson-bookmark-delete">Excluir</button>
                                                                                `;
                                row.querySelector('p').textContent = result.bookmark.note || '';
                                bookmarkList.prepend(row);
                                bookmarkNote.value = '';
                                renderEmptyState();
                            } catch (e) {
                                Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível salvar o marcador.' });
                            } finally {
                                bookmarkAddBtn.disabled = false;
                            }
                        });
                    }

                    if (bookmarkCurrentTime) {
                        bookmarkCurrentTime.addEventListener('click', () => {
                            onTimeTick();
                            if (bookmarkNote) {
                                bookmarkNote.focus();
                            }
                        });
                    }
                };

                const bindMedia = (api) => {
                    media = (api && api.media) ? api.media : null;
                    const player = (api && api.player) ? api.player : null;
                    if (!media && !player) return;

                    onTimeTick();

                    const applyResume = () => {
                        if (!media) return;
                        if (resumeApplied || resumeAt <= 0) return;
                        if (!Number.isFinite(media.duration) || media.duration <= 0) return;
                        media.currentTime = Math.min(resumeAt, Math.max(0, media.duration - 1));
                        resumeApplied = true;
                    };

                    if (media) {
                        if (media.readyState >= 1) {
                            applyResume();
                        } else {
                            media.addEventListener('loadedmetadata', applyResume, { once: true });
                        }
                    }

                    const onPlay = () => {
                        if (saveTimer) {
                            window.clearInterval(saveTimer);
                        }
                        saveTimer = window.setInterval(() => saveProgress(false), 15000);
                    };
                    const onPause = () => {
                        onTimeTick();
                        saveProgress(true);
                    };
                    const onEnded = () => {
                        onTimeTick();
                        saveProgress(true);
                        if (saveTimer) {
                            window.clearInterval(saveTimer);
                            saveTimer = null;
                        }
                    };

                    if (media) {
                        media.addEventListener('loadedmetadata', onTimeTick);
                        media.addEventListener('timeupdate', onTimeTick);
                        media.addEventListener('pause', onPause);
                        media.addEventListener('ended', onEnded);
                        media.addEventListener('play', onPlay);
                    }

                    if (player && typeof player.on === 'function') {
                        player.on('timeupdate', onTimeTick);
                        player.on('pause', onPause);
                        player.on('ended', onEnded);
                        player.on('play', onPlay);
                        player.on('ready', () => {
                            applyResume();
                            onTimeTick();
                        });
                    }

                    bindBookmarkActions();
                };

                const existingApi = wrapper.__unnVideoApi || null;
                if (existingApi) {
                    bindMedia(existingApi);
                } else {
                    wrapper.addEventListener('unn:video-ready', (event) => bindMedia((event && event.detail) ? event.detail : null), { once: true });
                }

                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'hidden') {
                        saveProgress(true, true);
                    }
                });
                window.addEventListener('pagehide', () => saveProgress(true, true));
                window.addEventListener('beforeunload', () => saveProgress(true, true));

                renderEmptyState();

                // Course Completion Handler with SweetAlert2
                const btnComplete = document.getElementById('btn-complete-course');
                const formComplete = document.getElementById('form-complete-course');
                if (btnComplete && formComplete) {
                    btnComplete.addEventListener('click', function (e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Concluir Curso?',
                            text: 'Tem certeza que deseja marcar este curso como concluído?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Sim, concluir!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                formComplete.submit();
                            }
                        });
                    });
                }

            })();
        </script>
    @endauth
@endpush