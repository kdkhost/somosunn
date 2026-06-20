@extends('panel.layouts.app')

@section('title', $company->exists ? 'Editar Empresa' : 'Nova Empresa')

@php
    $members = old('members', $company->memberships->map(fn($membership) => ['user_id' => $membership->user_id, 'role' => $membership->role])->values()->all() ?: [['user_id' => '', 'role' => 'owner']]);
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $company->exists ? 'Editar empresa' : 'Nova empresa' }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Cadastro empresarial para publicacao e patrocinio.</p>
            </div>
            <a href="{{ route('panel.admin.companies.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">Voltar</a>
        </div>

        <form method="POST" enctype="multipart/form-data" action="{{ $company->exists ? route('panel.admin.companies.update', $company) : route('panel.admin.companies.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @if($company->exists) @method('PUT') @endif
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-semibold">Nome</label><input type="text" name="name" value="{{ old('name', $company->name) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950" required></div>
                <div><label class="mb-2 block text-sm font-semibold">Slug</label><input type="text" name="slug" value="{{ old('slug', $company->slug) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">CNPJ</label><input type="text" name="cnpj" value="{{ old('cnpj', $company->cnpj) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">E-mail</label><input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Telefone</label><input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">WhatsApp</label><input type="text" name="whatsapp" value="{{ old('whatsapp', $company->whatsapp) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Cidade</label><input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Estado</label><input type="text" name="state" value="{{ old('state', $company->state) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Site</label><input type="url" name="website" value="{{ old('website', $company->website) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">Instagram</label><input type="url" name="instagram" value="{{ old('instagram', $company->instagram) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">LinkedIn</label><input type="url" name="linkedin" value="{{ old('linkedin', $company->linkedin) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div><label class="mb-2 block text-sm font-semibold">YouTube</label><input type="url" name="youtube" value="{{ old('youtube', $company->youtube) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-semibold">Descricao</label><textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">{{ old('description', $company->description) }}</textarea></div>
                <div><label class="mb-2 block text-sm font-semibold">Logo</label><input type="file" name="logo" class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 dark:border-slate-700"></div>
                <div><label class="mb-2 block text-sm font-semibold">Banner</label><input type="file" name="banner" class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 dark:border-slate-700"></div>
                <div class="md:col-span-2">
                    <h2 class="mb-3 text-lg font-black">Equipe vinculada</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($members as $index => $member)
                            <div><label class="mb-2 block text-sm font-semibold">Membro {{ $index + 1 }}</label><select name="members[{{ $index }}][user_id]" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"><option value="">Selecione</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) data_get($member, 'user_id') === (string) $user->id)>{{ $user->name }} - {{ $user->email }}</option>@endforeach</select></div>
                            <div><label class="mb-2 block text-sm font-semibold">Papel</label><select name="members[{{ $index }}][role]" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">@foreach(['owner' => 'Owner', 'manager' => 'Manager', 'staff' => 'Staff'] as $value => $label)<option value="{{ $value }}" @selected(data_get($member, 'role', 'staff') === $value)>{{ $label }}</option>@endforeach</select></div>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-3"><input type="checkbox" name="verified" value="1" @checked(old('verified', $company->verified))><span class="text-sm font-semibold">Empresa verificada</span></div>
                <div class="flex items-center gap-3"><input type="checkbox" name="active" value="1" @checked(old('active', $company->active ?? true))><span class="text-sm font-semibold">Empresa ativa</span></div>
            </div>
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Salvar empresa</button></div>
        </form>
    </div>
@endsection
