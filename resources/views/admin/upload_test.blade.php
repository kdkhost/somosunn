@extends('admin.layouts.app')

@section('page_title','Teste de Upload')

@section('content')
<div class="card"><div class="card-body">
    <form id="uploadForm" enctype="multipart/form-data">
        <div class="form-group"><input type="file" name="file" id="fileInput" class="form-control" /></div>
        <button type="button" id="btnUpload" class="btn btn-primary">Enviar (chunked)</button>
    </form>
    <div id="result" class="mt-3"></div>
</div></div>

@push('scripts')
<script>
    function uuidv4(){ return 'xxxxxx'.replace(/x/g,()=>Math.floor(Math.random()*16).toString(16)); }

    document.getElementById('btnUpload').addEventListener('click', async function(){
        var f = document.getElementById('fileInput').files[0];
        if(!f) return alert('Escolha um arquivo');

        var chunkSize = 5 * 1024 * 1024; // 5MB
        var total = Math.ceil(f.size / chunkSize);
        var uploadId = uuidv4()+Date.now();

        for(var i=0;i<total;i++){
            var start = i*chunkSize;
            var end = Math.min(start+chunkSize, f.size);
            var blob = f.slice(start,end);
            var fd = new FormData(); fd.append('file', blob); fd.append('upload_id', uploadId); fd.append('chunk_index', i); fd.append('total_chunks', total);
            document.getElementById('result').innerText = `Enviando chunk ${i+1}/${total}`;
            await fetch('/upload/chunk', { method:'POST', body: fd, headers: { 'Accept':'application/json' } });
        }

        // assemble
        var res = await fetch('/upload/assemble', { method: 'POST', headers: { 'Accept':'application/json','Content-Type':'application/json' }, body: JSON.stringify({ upload_id: uploadId, filename: f.name, total_chunks: total }) });
        var json = await res.json();
        document.getElementById('result').innerText = JSON.stringify(json);
    });
</script>
@endpush

@endsection