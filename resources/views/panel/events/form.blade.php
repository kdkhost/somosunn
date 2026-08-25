@extends('member.layout')
@section('title', isset($event) && $event->id ? 'Editar Evento' : 'Novo Evento')
@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-blue-900 mb-6">
            {{ isset($event) && $event->id ? 'Editar Evento' : 'Novo Evento' }}
        </h1>
        <form method="POST"
            action="{{ isset($event) && $event->id ? route('panel.events.update', $event) : route('panel.events.store') }}"
            enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if(isset($event) && $event->id)
                @method('PUT')
            @endif
            <div>
                <label class="block text-sm font-semibold mb-1">Título</label>
                <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required maxlength="255"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Descrição curta</label>
                <input type="text" name="short_description"
                    value="{{ old('short_description', $event->short_description ?? '') }}" maxlength="500"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Descrição completa</label>
                <textarea name="full_description" rows="5"
                    class="w-full border rounded px-3 py-2">{{ old('full_description', $event->full_description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Data</label>
                <input type="datetime-local" name="start_at"
                    value="{{ old('start_at', isset($event->start_at) ? $event->start_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2" required>
                    <option value="draft" @if(old('status', $event->status ?? '') == 'draft') selected @endif>Rascunho
                    </option>
                    <option value="published" @if(old('status', $event->status ?? '') == 'published') selected @endif>
                        Publicado</option>
                    <option value="archived" @if(old('status', $event->status ?? '') == 'archived') selected @endif>Arquivado
                    </option>
                    <option value="paused" @if(old('status', $event->status ?? '') == 'paused') selected @endif>Pausado
                    </option>
                </select>
            </div>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-2">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_ticket_enabled" value="1"
                        class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ old('is_ticket_enabled', $event->is_ticket_enabled ?? false) ? 'checked' : '' }}>
                    <span class="text-md font-bold text-gray-800"><i class="fas fa-qrcode text-blue-600 mr-2"></i>Habilitar
                        Validação de Entrada por QR Code</span>
                </label>
                <p class="text-sm text-gray-500 ml-9">Gera um ingresso digital por participante para validação/check-in,
                    contabilizando pontos ao organizador e participante.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Imagem</label>
                <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
                @if($event->image_url)
                    <img src="{{ $event->image_url }}" alt="Imagem" class="h-20 mt-2">
                @endif
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('panel.events.index') }}"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Salvar
                    Evento</button>
            </div>
        </form>

        @if($event->exists)
            <hr class="my-8">
            <div>
                <h3 class="text-xl font-bold mb-4">Galeria de Fotos e Vídeos</h3>
                <p class="text-sm text-gray-500 mb-4">Faça o upload de fotos ou vídeos do evento. As imagens receberão uma marca
                    d'água automaticamente com o nome do organizador e a data.</p>

                <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-black text-slate-900">Armazenamento e acesso rapido</p>
                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            As imagens ficam em <code>storage/app/public/events/{{ $event->id }}/gallery</code> e os videos em <code>storage/app/public/events/{{ $event->id }}/gallery/videos</code>.
                        </p>
                    </div>
                    <a href="{{ route('panel.gallery.index', ['event_id' => $event->id]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-blue-200 bg-white px-4 py-2 text-sm font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                        <i class="fas fa-images"></i>
                        Abrir galeria completa do evento
                    </a>
                </div>

                <div class="mb-4">
                    <input type="file" id="galleryInput" multiple accept="image/*,video/*" class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                        " />
                    <button type="button" onclick="uploadGallery()" id="btnUploadGallery"
                        class="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm w-full md:w-auto">
                        <i class="fas fa-upload mr-2"></i> Enviar Arquivos Selecionados
                    </button>
                </div>

                <!-- Existing Media -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="galleryContainer">
                    @foreach($event->media as $media)
                        <div class="relative bg-gray-100 rounded border group" id="media-{{ $media->id }}">
                            @if($media->type === 'image')
                                <img src="{{ asset('storage/' . $media->file_path) }}" class="w-full h-32 object-cover rounded">
                            @else
                                <div class="w-full h-32 flex items-center justify-center bg-gray-800 rounded">
                                    <i class="fas fa-video text-white text-3xl"></i>
                                </div>
                            @endif
                            <button type="button" onclick="deleteMedia({{ $media->id }})"
                                class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div id="galleryEmptyState" class="@if($event->media->isNotEmpty()) hidden @endif mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm font-medium text-slate-500">
                    Nenhuma midia enviada ainda.
                </div>
            </div>
        @endif
    </div>
@endsection

@if($event->exists)
    @push('scripts')
        <script>
            function renderGalleryMediaCard(media) {
                const preview = media.type === 'image'
                    ? `<img src="${media.url}" class="w-full h-32 object-cover rounded">`
                    : `<div class="w-full h-32 flex items-center justify-center bg-gray-800 rounded"><i class="fas fa-video text-white text-3xl"></i></div>`;

                return `
                    <div class="relative bg-gray-100 rounded border group" id="media-${media.id}">
                        ${preview}
                        <button type="button" onclick="deleteMedia(${media.id})"
                            class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }

            function appendGalleryMedia(mediaItems) {
                const container = document.getElementById('galleryContainer');
                const emptyState = document.getElementById('galleryEmptyState');

                if (!container || !Array.isArray(mediaItems) || mediaItems.length === 0) {
                    return;
                }

                mediaItems.slice().reverse().forEach((media) => {
                    container.insertAdjacentHTML('afterbegin', renderGalleryMediaCard(media));
                });

                if (emptyState) {
                    emptyState.classList.add('hidden');
                }
            }

            async function uploadGallery() {
                const input = document.getElementById('galleryInput');
                if (!input.files || input.files.length === 0) {
                    Swal.fire('Aviso', 'Selecione pelo menos um arquivo.', 'warning');
                    return;
                }

                const formData = new FormData();
                for (let i = 0; i < input.files.length; i++) {
                    formData.append('files[]', input.files[i]);
                }

                const btn = document.getElementById('btnUploadGallery');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                btn.disabled = true;

                try {
                    const response = await fetch('{{ route("panel.events.media.store", $event) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        appendGalleryMedia(data.media || []);
                        Swal.fire('Sucesso!', data.message, 'success');
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao enviar arquivos.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
                } finally {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    input.value = '';
                }
            }

            async function deleteMedia(id) {
                const confirmed = await window.showConfirmDialog({
                    title: 'Excluir mídia?',
                    text: 'Tem certeza que deseja apagar esta mídia?',
                    icon: 'warning'
                });

                if (!confirmed) return;

                try {
                    const response = await fetch(`/painel/events/{{ $event->id }}/media/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        document.getElementById(`media-${id}`).remove();
                        const container = document.getElementById('galleryContainer');
                        const emptyState = document.getElementById('galleryEmptyState');
                        if (container && emptyState && container.children.length === 0) {
                            emptyState.classList.remove('hidden');
                        }
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao excluir mídia.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
                }
            }
        </script>
    @endpush
@endif
