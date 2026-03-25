@extends('panel.layouts.app')

@section('title', 'Minha loja - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Loja virtual</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Minha loja premium</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Configure a identidade da sua marca, confirme o slug publico e publique sua vitrine.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if($storeUrl)
                        <a href="{{ $storeUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                            <i class="fas fa-up-right-from-square text-slate-400"></i> Ver loja
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold {{ $store->is_published ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        <i class="fas {{ $store->is_published ? 'fa-circle-check' : 'fa-pen-ruler' }}"></i>
                        {{ $store->is_published ? 'Loja publicada' : 'Loja em rascunho' }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('panel.marketplace.store.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid gap-6 xl:grid-cols-[1.4fr,0.8fr]">
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nome da marca</label>
                                <input type="text" name="brand_name" value="{{ old('brand_name', $store->brand_name) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('brand_name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Slogan</label>
                                <input type="text" name="tagline" value="{{ old('tagline', $store->tagline) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Slug publico</label>
                                <input type="text" name="slug" value="{{ old('slug', $store->slug) }}" {{ $store->isSlugLocked() ? 'readonly' : '' }} class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Sua loja abrira em <strong>/loja/{{ old('slug', $store->slug ?: 'sua-marca') }}</strong>. Depois da primeira publicacao o slug fica travado.</p>
                                @error('slug')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Publicacao</label>
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $store->is_published) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Publicar loja</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor primaria</label>
                                <input type="text" name="primary_color" value="{{ old('primary_color', $store->primary_color) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor de destaque</label>
                                <input type="text" name="accent_color" value="{{ old('accent_color', $store->accent_color) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Bio da loja</label>
                                <textarea name="bio" rows="6" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('bio', $store->bio) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Contato e redes</h2>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <input type="email" name="support_email" value="{{ old('support_email', $store->support_email) }}" placeholder="E-mail de atendimento" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="text" name="support_phone" value="{{ old('support_phone', $store->support_phone) }}" placeholder="Telefone de atendimento" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}" placeholder="WhatsApp" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="website_url" value="{{ old('website_url', $store->website_url) }}" placeholder="Site da marca" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="instagram_url" value="{{ old('instagram_url', $store->instagram_url) }}" placeholder="Instagram" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="facebook_url" value="{{ old('facebook_url', $store->facebook_url) }}" placeholder="Facebook" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            <input type="url" name="youtube_url" value="{{ old('youtube_url', $store->youtube_url) }}" placeholder="YouTube" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white md:col-span-2">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Logo</h2>
                        @if($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="Logo" class="mt-4 h-28 w-28 rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_logo" value="0">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-500"> Remover logo atual
                        </label>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Banner</h2>
                        @if($store->banner_url)
                            <img src="{{ $store->banner_url }}" alt="Banner" class="mt-4 h-40 w-full rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="banner" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_banner" value="0">
                            <input type="checkbox" name="remove_banner" value="1" class="rounded border-slate-300 text-red-500"> Remover banner atual
                        </label>
                    </div>

                    <div class="text-white rounded-3xl p-6 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, {{ $store->primary_color ?: '#1F5EDB' }}, {{ $store->accent_color ?: '#0F172A' }});">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-white/70">Preview</p>
                        <h3 class="mt-3 text-2xl font-black">{{ old('brand_name', $store->brand_name) ?: 'Sua marca' }}</h3>
                        <p class="mt-2 text-sm text-white/80">{{ old('tagline', $store->tagline) ?: 'Loja premium dentro do ecossistema UNN.' }}</p>
                        <p class="mt-4 text-sm text-white/70">Esse bloco mostra como a sua loja vai aparecer no topo da vitrine publica.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                    <i class="fas fa-save"></i> Salvar loja
                </button>
            </div>
        </form>
    </div>
@endsection
