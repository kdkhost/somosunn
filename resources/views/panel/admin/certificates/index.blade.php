@extends('panel.layouts.app')

@section('title', 'Gestão de Certificados')

@section('content')
    <div x-data="{ tab: 'issued' }" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Certificados</h1>
                <p class="text-sm text-slate-500 mt-1">Gerencie a emissão e consulta de certificados dos seus alunos.</p>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="bg-white p-1 rounded-2xl shadow-sm border border-slate-200 inline-flex items-center gap-1">
            <button @click="tab = 'issued'"
                :class="tab === 'issued' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-certificate"></i>
                <span>Emitidos</span>
            </button>
            <button @click="tab = 'pending'"
                :class="tab === 'pending' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-clock"></i>
                <span>Pendentes</span>
                @if($pendingEnrollments->count() > 0)
                    <span class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1">
                        {{ $pendingEnrollments->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Issued Certificates --}}
        <div x-show="tab === 'issued'" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Aluno</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Produto</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Data Emissão
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($issuedCertificates as $cert)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold">
                                            {{ substr($cert->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $cert->user->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $cert->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cert->course_id ? 'bg-emerald-100 text-emerald-700' : ($cert->mentorship_id ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }} mr-2">
                                        {{ $cert->course_id ? 'Curso' : ($cert->mentorship_id ? 'Mentoria' : 'Evento') }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $cert->course->title ?? $cert->mentorship->title ?? $cert->event->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $cert->issued_at ? $cert->issued_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.certificates.view', $cert->cert_hash) }}" target="_blank"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.certificates.destroy', $cert) }}" method="POST"
                                            onsubmit="return confirm('Excluir certificado permanentemente?')">
                                            @csrf @method('DELETE')
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition-all">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-certificate text-slate-200 text-4xl mb-4"></i>
                                        <p class="text-slate-500 font-medium">Nenhum certificado emitido até agora.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issuedCertificates->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200">
                    {{ $issuedCertificates->links() }}
                </div>
            @endif
        </div>

        {{-- Pending Certificates --}}
        <div x-show="tab === 'pending'" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Aluno</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Produto</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Concluído em
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Ação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingEnrollments as $enrollment)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold">
                                            {{ substr($enrollment->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $enrollment->user->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $enrollment->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $enrollment->enrollable_type === 'App\Models\Course' ? 'bg-emerald-100 text-emerald-700' : ($enrollment->enrollable_type === 'App\Models\Mentorship' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }} mr-2">
                                        {{ $enrollment->enrollable_type === 'App\Models\Course' ? 'Curso' : ($enrollment->enrollable_type === 'App\Models\Mentorship' ? 'Mentoria' : 'Evento') }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $enrollment->enrollable->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $enrollment->completed_at ? $enrollment->completed_at->format('d/m/Y') : 'Recentemente' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('panel.admin.certificates.generate') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $enrollment->user_id }}">
                                        @if($enrollment->enrollable_type === 'App\Models\Course')
                                            <input type="hidden" name="course_id" value="{{ $enrollment->enrollable_id }}">
                                        @elseif($enrollment->enrollable_type === 'App\Models\Mentorship')
                                            <input type="hidden" name="mentorship_id" value="{{ $enrollment->enrollable_id }}">
                                        @else
                                            <input type="hidden" name="event_id" value="{{ $enrollment->enrollable_id }}">
                                        @endif
                                        <button
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-100">
                                            Emitir Agora
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-check-circle text-emerald-100 text-4xl mb-4"></i>
                                        <p class="text-slate-500 font-medium">Todos os certificados foram emitidos!</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection