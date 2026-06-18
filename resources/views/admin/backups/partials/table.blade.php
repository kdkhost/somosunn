@if(count($items) === 0)
    <div class="backup-empty m-3">
        <i class="fas fa-folder-open fa-2x mb-2"></i>
        <div>Nenhum backup encontrado nesta categoria.</div>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover backup-table mb-0">
            <thead>
                <tr>
                    <th>Arquivo</th>
                    <th>Disco</th>
                    <th>Tamanho</th>
                    <th>Gerado em</th>
                    <th class="text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ basename($item['path']) }}</div>
                            <div class="backup-path text-muted">{{ $item['path'] }}</div>
                        </td>
                        <td>
                            @if(($item['disk'] ?? '') === 'local')
                                <span class="badge badge-info"><i class="fas fa-hdd mr-1"></i> Local</span>
                            @else
                                <span class="badge badge-primary"><i class="fas fa-cloud mr-1"></i> S3</span>
                            @endif
                        </td>
                        <td>{{ $item['size_label'] }}</td>
                        <td>{{ $item['modified_label'] }}</td>
                        <td>
                            <div class="backup-actions">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('admin.backups.download', ['type' => $item['type'], 'disk' => $item['disk'], 'path' => $item['path']]) }}">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('admin.backups.destroy') }}" method="POST" class="backup-delete-form d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="{{ $item['type'] }}">
                                    <input type="hidden" name="disk" value="{{ $item['disk'] }}">
                                    <input type="hidden" name="path" value="{{ $item['path'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
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
