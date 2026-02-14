@extends('member.layout')
@section('title', 'Meus Certificados')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Meus Certificados</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($certificates as $certificate)
            <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-certificate text-blue-700 text-2xl"></i>
                    <span class="font-semibold text-lg">{{ $certificate->title }}</span>
                </div>
                <div class="text-gray-600 mb-2">Emitido em: {{ $certificate->created_at->format('d/m/Y') }}</div>
                <div class="flex-1"></div>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('panel.certificates.show', $certificate) }}" class="text-blue-700 hover:underline">Visualizar</a>
                    <a href="{{ route('panel.certificates.download', $certificate) }}" class="text-green-700 hover:underline ml-2">Baixar PDF</a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-gray-500">Nenhum certificado encontrado.</div>
        @endforelse
    </div>
</div>
@endsection
