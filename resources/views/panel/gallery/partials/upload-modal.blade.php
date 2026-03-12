@php
    $galleryUploadPerFileLimitBytes = $galleryUploadPerFileLimitBytes
        ?? (\App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024));
    $galleryUploadPerFileLimitMb = number_format($galleryUploadPerFileLimitBytes / 1024 / 1024, 2, '.', '');
@endphp

<div id="gallery-upload-modal" class="fixed inset-0 z-[90] hidden">
    <div data-gallery-close-upload class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

    <div class="relative flex min-h-full items-start justify-center p-4 md:items-center md:p-8">
        <div class="gallery-modal-panel relative flex w-full max-w-3xl max-h-[calc(100vh-2rem)] flex-col overflow-hidden rounded-[2.2rem] border border-slate-200 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.28)] dark:border-slate-800 dark:bg-slate-900 md:max-h-[calc(100vh-4rem)]">
            <div class="relative overflow-hidden border-b border-slate-100 px-6 py-6 dark:border-slate-800 md:px-8">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.12),_transparent_28%),radial-gradient(circle_at_80%_0,_rgba(14,165,233,0.12),_transparent_24%)]"></div>
                <div class="relative flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-[1.4rem] bg-blue-600 text-xl text-white shadow-lg shadow-blue-500/20">
                            <i class="fas fa-cloud-arrow-up"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-blue-600 dark:text-blue-400">Nova contribuicao</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Enviar fotos para a galeria</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                Use o mesmo padrao visual do painel para enviar varias fotos de um evento com feedback de progresso e retorno imediato.
                            </p>
                        </div>
                    </div>

                    <button type="button"
                        data-gallery-close-upload
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:text-white">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <form id="gallery-upload-form"
                action="{{ route('panel.gallery.upload') }}"
                method="POST"
                enctype="multipart/form-data"
                data-panel-upload-progress="false"
                novalidate
                class="flex min-h-0 flex-1 flex-col">
                @csrf

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6 md:px-8 md:py-8">
                    <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_18rem]">
                        <div class="space-y-6">
                            <div>
                                <label for="gallery-upload-event" class="mb-2 block text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                    Evento associado
                                </label>
                                <div class="relative">
                                    <select id="gallery-upload-event" name="event_id"
                                        class="w-full appearance-none rounded-[1.6rem] border border-slate-200 bg-slate-50 px-5 py-4 pr-12 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-500/10">
                                        <option value="">Selecione um evento...</option>
                                        @foreach($events as $event)
                                            <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                                                {{ $event->title }}@if($event->start_at) - {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-5 flex items-center text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </span>
                                </div>
                            </div>

                            <div id="gallery-dropzone"
                                class="gallery-dropzone rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50 p-6 transition dark:border-slate-700 dark:bg-slate-950">
                                <input id="gallery-files-input" type="file" name="files[]" accept="image/jpeg,image/png,image/jpg,image/webp" multiple
                                    class="hidden" data-filepond-ignore="true">

                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.6rem] bg-blue-600 text-2xl text-white shadow-lg shadow-blue-500/20">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <h3 class="mt-5 text-xl font-black text-slate-900 dark:text-white">Arraste suas fotos aqui</h3>
                                    <p class="mt-2 max-w-xl text-sm leading-7 text-slate-500 dark:text-slate-400">
                                        Clique na area abaixo ou solte varias imagens. O envio acontece uma foto por vez e respeita o limite efetivo de {{ $galleryUploadPerFileLimitMb }} MB por arquivo.
                                    </p>

                                    <button type="button"
                                        id="gallery-file-picker"
                                        class="mt-6 inline-flex items-center gap-3 rounded-[1.4rem] bg-slate-950 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:bg-slate-900 dark:bg-blue-600 dark:hover:bg-blue-500">
                                        <i class="fas fa-folder-open"></i>
                                        Selecionar arquivos
                                    </button>
                                </div>

                                <div class="mt-6 hidden rounded-[1.4rem] border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900" id="gallery-selected-files">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Arquivos prontos</p>
                                            <p id="gallery-selected-summary" class="mt-2 text-sm font-bold text-slate-800 dark:text-slate-100"></p>
                                        </div>
                                        <span id="gallery-selected-size" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-slate-500 dark:bg-slate-800 dark:text-slate-300"></span>
                                    </div>
                                    <div id="gallery-selected-list" class="mt-4 grid max-h-72 gap-2 overflow-y-auto pr-1 text-sm text-slate-500 dark:text-slate-400"></div>
                                </div>
                            </div>
                        </div>

                        <aside class="space-y-4">
                            <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Checklist</p>
                                <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    <li class="flex gap-3">
                                        <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        Associe o upload ao evento correto para organizar a cobertura.
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        Cada arquivo respeita o limite efetivo de {{ $galleryUploadPerFileLimitMb }} MB no servidor.
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        O painel publica uma imagem por vez para evitar recusas por lote excessivo.
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        As imagens recebem watermark quando o processamento do evento estiver ativo.
                                    </li>
                                </ul>
                            </div>

                            <div class="overflow-hidden rounded-[1.8rem] border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/40 dark:bg-blue-900/20">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700 dark:text-blue-300">Status do envio</p>
                                <div class="mt-4 rounded-full bg-blue-100 dark:bg-blue-950/50">
                                    <div id="gallery-upload-progress-bar" class="h-2 rounded-full bg-blue-600 transition-all" style="width: 0%"></div>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">
                                    <span id="gallery-upload-progress-label">Aguardando arquivos</span>
                                    <span id="gallery-upload-progress-value">0%</span>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-6 dark:border-slate-800 dark:bg-slate-900 md:px-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button"
                            data-gallery-close-upload
                            class="inline-flex items-center justify-center gap-3 rounded-[1.5rem] border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:text-white">
                            <i class="fas fa-xmark"></i>
                            Cancelar
                        </button>
                        <button type="submit"
                            id="gallery-upload-submit"
                            class="inline-flex items-center justify-center gap-3 rounded-[1.5rem] bg-blue-600 px-7 py-4 text-sm font-black uppercase tracking-[0.16em] text-white shadow-[0_18px_40px_rgba(37,99,235,0.28)] transition hover:-translate-y-0.5 hover:bg-blue-500">
                            <i class="fas fa-paper-plane"></i>
                            Publicar na galeria
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
