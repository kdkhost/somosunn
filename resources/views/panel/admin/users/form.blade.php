@extends('panel.layouts.app')

@section('title', ($user->id ? 'Editar' : 'Novo') . ' Usuário')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.users.index') }}" class="hover:underline transition-all">Usuários</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700 transition-colors">/</span>
    <span class="text-slate-500 dark:text-slate-400 transition-colors">{{ $user->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form action="{{ $user->id ? route('panel.admin.users.update', $user) : route('panel.admin.users.store') }}" method="POST">
        @csrf
        @if($user->id)
            @method('PUT')
        @endif

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight transition-colors">
                        {{ $user->id ? 'Editar' : 'Novo' }} Usuário
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors mt-1 font-medium">Gerencie as informações e permissões de acesso do usuário.</p>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="{{ route('panel.admin.users.index') }}" 
                       class="flex-1 sm:flex-none text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold py-3 px-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl shadow-xl shadow-blue-500/20 transition transform hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fas fa-save mr-2"></i> Salvar Usuário
                    </button>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 text-red-600 dark:text-red-400 px-6 py-4 rounded-3xl flex items-start gap-4 transition-all">
                    <i class="fas fa-exclamation-triangle mt-1 text-lg"></i>
                    <div>
                        <p class="font-bold text-lg mb-1 tracking-tight">Ops! Encontramos alguns problemas:</p>
                        <ul class="list-disc list-inside text-sm font-medium opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Dados Pessoais</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Nome Completo</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ex: João Silva"
                                       class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white placeholder:text-slate-400">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">E-mail de Acesso</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="email@exemplo.com"
                                       class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white placeholder:text-slate-400">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">
                                    Nova Senha 
                                    @if($user->id) <span class="text-[10px] font-medium text-slate-400 normal-case ml-2 italic tracking-normal">(deixe em branco se não quiser alterar)</span> @endif
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <input type="password" name="password" {{ !$user->id ? 'required' : '' }} placeholder="{{ !$user->id ? 'Crie uma senha forte' : '••••••••' }}"
                                           class="w-full pl-12 pr-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions / Features -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Permissões e Recursos</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Ative funcionalidades exclusivas para este usuário.</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($userFeatures as $key => $label)
                                <label class="flex items-start gap-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 hover:bg-white dark:hover:bg-slate-900 hover:border-blue-200 dark:hover:border-blue-900 transition-all cursor-pointer group hover:shadow-sm">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="checkbox" name="extra_features[]" value="{{ $key }}"
                                            {{ in_array($key, old('extra_features', $user->extra_features ?? [])) ? 'checked' : '' }}
                                            class="w-5 h-5 text-blue-600 border-slate-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 dark:bg-slate-950 dark:checked:bg-blue-600 transition-all">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">{{ $label }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono mt-1 uppercase tracking-tight">{{ $key }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="space-y-8">
                    <!-- Access Control -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xs">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Nível de Acesso</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Papel (Role)</label>
                                <select name="role" class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white">
                                    <option value="membro" {{ old('role', $user->role) == 'membro' ? 'selected' : '' }}>Membro</option>
                                    <option value="instrutor" {{ old('role', $user->role) == 'instrutor' ? 'selected' : '' }}>Instrutor</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    @if($canSetSuperadmin)
                                        <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Patente (Gamification)</label>
                                <select name="level" class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white">
                                    <option value="iniciante" {{ old('level', $user->level) == 'iniciante' ? 'selected' : '' }}>Iniciante</option>
                                    <option value="bronze" {{ old('level', $user->level) == 'bronze' ? 'selected' : '' }}>Bronze</option>
                                    <option value="prata" {{ old('level', $user->level) == 'prata' ? 'selected' : '' }}>Prata</option>
                                    <option value="ouro" {{ old('level', $user->level) == 'ouro' ? 'selected' : '' }}>Ouro</option>
                                    <option value="diamante" {{ old('level', $user->level) == 'diamante' ? 'selected' : '' }}>Diamante</option>
                                    <option value="sucesso" {{ old('level', $user->level) == 'sucesso' ? 'selected' : '' }}>Sucesso</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Control -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Assinatura</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Plano Ativo</label>
                                <select name="plan_id" class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white">
                                    <option value="">-- Sem Plano --</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id', $user->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Data de Expiração</label>
                                <input type="date" name="plan_expires_at" 
                                       value="{{ old('plan_expires_at', $user->plan_expires_at ? $user->plan_expires_at->format('Y-m-d') : '') }}"
                                       class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all font-bold text-slate-800 dark:text-white">
                                <p class="text-[10px] text-slate-400 mt-2 font-medium">Assinatura validará automaticamente após esta data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
