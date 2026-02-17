@extends('panel.layouts.app')

@section('title', ($user->id ? 'Editar' : 'Novo') . ' Usuário')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.users.index') }}" class="hover:underline">Usuários</a>
    <span class="mx-2">/</span>
    <span class="text-slate-500">{{ $user->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form action="{{ $user->id ? route('panel.admin.users.update', $user) : route('panel.admin.users.store') }}" method="POST">
        @csrf
        @if($user->id)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white transition-colors">
                        {{ $user->id ? 'Editar' : 'Novo' }} Usuário
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">Gerencie as informações e permissões de acesso.</p>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="{{ route('panel.admin.users.index') }}" 
                       class="flex-1 sm:flex-none text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold py-2 px-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl flex items-start gap-3 transition-colors">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-bold">Atenção!</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-6 transition-colors duration-300">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">Dados Pessoais</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nome Completo</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">E-mail</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                       class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Senha 
                                    @if($user->id) <span class="font-normal text-slate-400 dark:text-slate-500 text-xs ml-1">(deixe em branco para manter)</span> @endif
                                </label>
                                <input type="password" name="password" {{ !$user->id ? 'required' : '' }}
                                       class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Permissions / Features -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                            Permissões e Recursos
                            <span class="text-xs font-normal text-slate-400 dark:text-slate-500 ml-2">Liberar acesso individual a funcionalidades</span>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($userFeatures as $key => $label)
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer group">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="extra_features[]" value="{{ $key }}"
                                            {{ in_array($key, old('extra_features', $user->extra_features ?? [])) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:bg-slate-900 dark:checked:bg-blue-600">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition">{{ $label }}</span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $key }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="space-y-6">
                    <!-- Access Control -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4 transition-colors duration-300">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">Controle de Acesso</h3>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Função (Role)</label>
                            <select name="role" class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                                <option value="membro" {{ old('role', $user->role) == 'membro' ? 'selected' : '' }}>Membro</option>
                                <option value="instrutor" {{ old('role', $user->role) == 'instrutor' ? 'selected' : '' }}>Instrutor</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                @if($canSetSuperadmin)
                                    <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                @endif
                            </select>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Define o nível hierárquico no sistema.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nível (Gamification)</label>
                            <select name="level" class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                                <option value="iniciante" {{ old('level', $user->level) == 'iniciante' ? 'selected' : '' }}>Iniciante</option>
                                <option value="bronze" {{ old('level', $user->level) == 'bronze' ? 'selected' : '' }}>Bronze</option>
                                <option value="prata" {{ old('level', $user->level) == 'prata' ? 'selected' : '' }}>Prata</option>
                                <option value="ouro" {{ old('level', $user->level) == 'ouro' ? 'selected' : '' }}>Ouro</option>
                                <option value="diamante" {{ old('level', $user->level) == 'diamante' ? 'selected' : '' }}>Diamante</option>
                                <option value="sucesso" {{ old('level', $user->level) == 'sucesso' ? 'selected' : '' }}>Sucesso</option>
                            </select>
                        </div>
                    </div>

                    <!-- Plan Control -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4 transition-colors duration-300">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">Plano e Assinatura</h3>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Plano Ativo</label>
                            <select name="plan_id" class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                                <option value="">-- Sem Plano --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id', $user->plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Expira em</label>
                            <input type="date" name="plan_expires_at" 
                                   value="{{ old('plan_expires_at', $user->plan_expires_at ? $user->plan_expires_at->format('Y-m-d') : '') }}"
                                   class="w-full rounded-xl border-slate-400 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all font-medium text-slate-800 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
