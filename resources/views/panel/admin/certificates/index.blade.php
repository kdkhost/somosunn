@extends('panel.layouts.app')

@section('title', 'Gestão de Certificados')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.certificates.index') }}" class="hover:underline">Certificados</a>
@endsection

@section('panel_content')
    <div x-data="{ tab: 'issued' }" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Certificados
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie a emissão e consulta
                    de certificados dos seus alunos.</p>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div
            class="bg-slate-100 dark:bg-slate-800/50 p-1 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 inline-flex items-center gap-1 transition-colors">
            <button @click="tab = 'issued'"
                :class="tab === 'issued' ? 'bg-white dark:bg-blue-600 text-blue-600 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-certificate"></i>
                <span>Emitidos</span>
            </button>
            <button @click="tab = 'pending'"
                :class="tab === 'pending' ? 'bg-white dark:bg-blue-600 text-blue-600 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-clock"></i>
                <span>Pendentes</span>
                @if($pendingEnrollments->count() > 0)
                    <span
                        class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1 shadow-sm shadow-blue-500/50">
                        {{ $pendingEnrollments->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Issued Certificates --}}
        <div x-show="tab === 'issued'"
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 transition-colors">
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Aluno</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Produto</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Data Emissão
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-right">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($issuedCertificates as $cert)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($cert->user->profile_photo_url && !str_contains($cert->user->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $cert->user->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white transition-colors">
                                                {{ $cert->user->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 transition-colors">
                                                {{ $cert->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cert->course_id ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50' : ($cert->mentorship_id ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50') }} mr-2 transition-colors">
                                        {{ $cert->course_id ? 'Curso' : ($cert->mentorship_id ? 'Mentoria' : 'Evento') }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300 transition-colors">
                                        {{ $cert->course->title ?? $cert->mentorship->title ?? $cert->event->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
                                    {{ $cert->issued_at ? $cert->issued_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">
                                        <a href="{{ route('admin.certificates.view', $cert->cert_hash) }}" target="_blank"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white dark:hover:text-white transition-all border border-transparent hover:border-blue-100 dark:hover:border-blue-900/40 shadow-sm"
                                            title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.certificates.destroy', $cert) }}" method="POST"
                                            onsubmit="return confirm('Excluir certificado permanentemente?')">
                                            @csrf @method('DELETE')
                                            <button
                                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 hover:bg-red-500 dark:hover:bg-red-500 hover:text-white dark:hover:text-white transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/40 shadow-sm"
                                                title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 transition-colors">
                                    <div class="flex flex-col items-center">
                                        <i
                                            class="fas fa-certificate text-slate-200 dark:text-slate-800 text-4xl mb-4 transition-colors"></i>
                                        <p class="font-medium">Nenhum certificado emitido até agora.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issuedCertificates->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800 transition-colors duration-300">
                    {{ $issuedCertificates->links() }}
                </div>
            @endif
        </div>

        {{-- Pending Certificates --}}
        <div x-show="tab === 'pending'"
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 transition-colors">
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Aluno</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Produto</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Concluído em
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-right">
                                Ação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($pendingEnrollments as $enrollment)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($enrollment->user->profile_photo_url && !str_contains($enrollment->user->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $enrollment->user->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white transition-colors">
                                                {{ $enrollment->user->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 transition-colors">
                                                {{ $enrollment->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $enrollment->enrollable_type === 'App\Models\Course' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50' : ($enrollment->enrollable_type === 'App\Models\Mentorship' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50') }} mr-2 transition-colors">
                                        {{ $enrollment->enrollable_type === 'App\Models\Course' ? 'Curso' : ($enrollment->enrollable_type === 'App\Models\Mentorship' ? 'Mentoria' : 'Evento') }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300 transition-colors">
                                        {{ $enrollment->enrollable->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
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
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                                            Emitir Agora
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 transition-colors">
                                    <div class="flex flex-col items-center">
                                        <i
                                            class="fas fa-check-circle text-emerald-100 dark:text-emerald-900/20 text-4xl mb-4 transition-colors"></i>
                                        <p class="font-medium">Todos os certificados foram emitidos!</p>
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