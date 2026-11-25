@if(count($items) === 0)
    <div class="backup-empty">
        <div class="backup-empty-icon">
            <i class="fas fa-folder-open"></i>
        </div>
        <h6 class="fw-bold mb-1">Nenhum backup encontrado</h6>
        <p class="text-secondary small mb-0">
            Gere um backup sob demanda nos botões acima ou aguarde a próxima execução agendada.
        </p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Arquivo</th>
                    <th>Armazenamento</th>
                    <th>Tamanho</th>
                    <th>Gerado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $fileName = basename($item['path']);
                        $isDb = ($item['type'] ?? 'db') === 'db';
                        $disk = ($item['disk'] ?? 's3') === 'local' ? 'local' : 's3';
                    @endphp
                    <tr class="backup-row" data-backup-search="{{ $fileName }} {{ $item['path'] }}" data-backup-disk="{{ $disk }}">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="backup-file-icon {{ $isDb ? 'is-db' : 'is-config' }}">
                                    <i class="fas {{ $isDb ? 'fa-database' : 'fa-file-zipper' }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="fw-bold text-truncate" style="max-width: 320px;">{{ $fileName }}</div>
                                    <div class="backup-path">{{ $item['path'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($disk === 'local')
                                <span class="badge rounded-pill text-bg-info"><i class="fas fa-hdd me-1"></i>Local</span>
                            @else
                                <span class="badge rounded-pill text-bg-primary"><i class="fas fa-cloud me-1"></i>Amazon S3</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $item['size_label'] }}</span>
                        </td>
                        <td>
                            <span class="text-secondary small">
                                <i class="far fa-clock me-1"></i>{{ $item['modified_label'] }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip"
                                   title="Baixar arquivo de backup"
                                   href="{{ route('admin.backups.download', ['type' => $item['type'], 'disk' => $item['disk'], 'path' => $item['path']]) }}">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('admin.backups.destroy') }}" method="POST" class="backup-delete-form d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="{{ $item['type'] }}">
                                    <input type="hidden" name="disk" value="{{ $item['disk'] }}">
                                    <input type="hidden" name="path" value="{{ $item['path'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Remover backup">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
