@extends('panel.layouts.app')

@section('title', 'Configurações')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.settings', ['group' => 'general']) }}" class="hover:underline">Configurações</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <!-- Settings Header & Navigation -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-bold text-slate-800">
                    Configurações do Sistema
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    Gerencie as configurações gerais, integrações e aparência da plataforma.
                </p>
            </div>

            <div class="flex overflow-x-auto no-scrollbar border-b border-slate-100">
                @php
                    $tabs = [
                        'general' => ['label' => 'Geral', 'icon' => 'fa-cogs'],
                        'gateway' => ['label' => 'Pagamentos', 'icon' => 'fa-credit-card'],
                        'smtp' => ['label' => 'SMTP (E-mail)', 'icon' => 'fa-envelope'],
                        // Add other groups as needed or migrate them gradually
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                        <a href="{{ route('panel.admin.settings', ['group' => $key]) }}" class="flex items-center gap-2 px-6 py-4 text-sm font-medium transition whitespace-nowrap border-b-2
                                          {{ $group === $key
                    ? 'border-blue-600 text-blue-600 bg-blue-50/50'
                    : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="fas {{ $tab['icon'] }}"></i>
                            {{ $tab['label'] }}
                        </a>
                @endforeach
            </div>
        </div>

        <!-- Settings Content -->
        <form action="{{ route('panel.admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="current_group" value="{{ $group }}">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            <div>
                                <h4 class="text-red-800 font-bold text-sm">Atenção!</h4>
                                <ul class="list-disc list-inside text-sm text-red-700 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span class="text-green-800 text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @include('panel.admin.settings.partials.' . $group)

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection