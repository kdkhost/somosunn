@extends('layouts.app')

@section('title', 'Área para expositor - ' . $event->title)

@section('content')
@php
    $batch = $currentBatch ?? null;
    $unitPrice = (float) ($batch['price'] ?? 0);
    $isPaid = $unitPrice > 0;
    $deadline = $batch['deadline'] ?? null;
    $selectedGateway = old('gateway');
    $activeGateways = $activeGateways ?? [];
    if (!$selectedGateway && count($activeGateways) === 1) {
        $selectedGateway = $activeGateways[0]['provider'];
    }

    $fieldLabelClasses = 'mb-2 block text-[11px] font-black uppercase tracking-[0.22em] text-slate-500';
    $fieldInputClasses = 'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100';
    $fieldErrorClasses = ' border-red-300 bg-red-50 text-red-900 focus:border-red-500 focus:ring-red-100';
@endphp

<div class="min-h-screen bg-slate-50 px-4 pb-20 pt-6 md:pt-28">
    <div class="mx-auto max-w-6xl">
        <a href="{{ route('events.show', $event) }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-blue-700">
            <i class="fas fa-arrow-left"></i> Voltar para o evento
        </a>

        <div class="grid gap-8 lg:grid-cols-3">
            <aside class="lg:col-span-1">
                <div class="sticky top-28 overflow-hidden rounded-2xl bg-white shadow-lg">
                    @if($event->exhibitor_area_image_url)
                        <img src="{{ $event->exhibitor_area_image_url }}" alt="Mapa da área de expositores" class="h-56 w-full object-cover">
                    @endif
                    <div class="p-6">
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-blue-700">
                            {{ $batch['label'] ?? 'Lote indisponível' }}
                        </span>
                        <h1 class="mt-4 text-2xl font-black text-slate-950">Área para expositor</h1>
                        <p class="mt-2 font-bold text-slate-900">{{ $event->title }}</p>

                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span>Preço unitário</span>
                                <strong class="text-lg text-slate-950">{{ 'R$ ' . number_format($unitPrice, 2, ',', '.') }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Vagas restantes</span>
                                <strong class="{{ $remainingSlots <= 3 ? 'text-red-600' : 'text-slate-950' }}">{{ (int) $remainingSlots }}</strong>
                            </div>
                            @if($deadline)
                                <div class="flex items-center justify-between">
                                    <span>Encerramento do lote</span>
                                    <strong class="text-slate-950">{{ $deadline->format('d/m/Y H:i') }}</strong>
                                </div>
                            @endif
                            @if($event->exhibitor_includes_ticket)
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 font-bold text-emerald-800">
                                    <i class="fas fa-ticket-alt mr-2"></i> Ingresso incluso no pacote
                                </div>
                            @endif
                        </div>

                        @if($event->exhibitor_description)
                            <div class="mt-5 border-t border-slate-100 pt-5 text-sm leading-relaxed text-slate-600">
                                {!! nl2br(e($event->exhibitor_description)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

            <main class="lg:col-span-2">
                @if(session('error'))
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 font-semibold text-red-700">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                        Revise os campos destacados para continuar.
                    </div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow-lg md:p-8">
                    <div class="mb-8 flex flex-col gap-4 border-b border-slate-100 pb-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-950">Dados do expositor</h2>
                            <p class="mt-1 text-sm text-slate-500">Preencha os dados do responsável e da marca para reservar a área.</p>
                        </div>
                        <a href="{{ route('events.checkout', $event) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-ticket-alt"></i> Comprar ingresso
                        </a>
                    </div>

                    <form action="{{ route('events.exhibitor.checkout', $event) }}" method="POST" class="space-y-6" data-no-ajax="true" data-no-spa="true" id="exhibitor-checkout-form">
                        @csrf

                        @if($isPaid && count($activeGateways) > 1)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="mb-3 text-xs font-black uppercase tracking-widest text-slate-500">Forma de pagamento</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach($activeGateways as $gateway)
                                        @php
                                            $provider = $gateway['provider'];
                                            $label = $provider === 'mercadopago' ? 'Mercado Pago' : 'SumUp';
                                            $icon = $provider === 'mercadopago' ? 'fas fa-handshake' : 'fas fa-credit-card';
                                        @endphp
                                        <label class="exhibitor-gateway-card flex cursor-pointer items-center gap-4 rounded-2xl border-2 p-4 transition {{ $selectedGateway === $provider ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                            <input type="radio" name="gateway" value="{{ $provider }}" class="sr-only exhibitor-gateway-radio" {{ $selectedGateway === $provider ? 'checked' : '' }}>
                                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-700 shadow-sm">
                                                <i class="{{ $icon }}"></i>
                                            </span>
                                            <span class="flex-1">
                                                <span class="block font-black text-slate-900">{{ $label }}</span>
                                                <span class="text-xs text-slate-500">Cartão, Pix e opções habilitadas no gateway</span>
                                            </span>
                                            <i class="fas fa-check-circle text-blue-600 {{ $selectedGateway === $provider ? '' : 'hidden' }}"></i>
                                        </label>
                                    @endforeach
                                </div>
                                @error('gateway')
                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif($isPaid && count($activeGateways) === 1)
                            <input type="hidden" name="gateway" value="{{ $activeGateways[0]['provider'] }}">
                        @elseif($isPaid)
                            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 font-semibold text-yellow-800">
                                Pagamento indisponível no momento. O organizador precisa ativar um gateway para vender áreas.
                            </div>
                        @endif

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="exhibitor_name" class="{{ $fieldLabelClasses }}">Nome do responsável</label>
                                <input id="exhibitor_name" name="name" type="text" required autocomplete="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="{{ $fieldInputClasses }}{{ $errors->has('name') ? $fieldErrorClasses : '' }}">
                                @error('name')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="exhibitor_email" class="{{ $fieldLabelClasses }}">E-mail</label>
                                <input id="exhibitor_email" name="email" type="email" required autocomplete="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="{{ $fieldInputClasses }}{{ $errors->has('email') ? $fieldErrorClasses : '' }}">
                                @error('email')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="exhibitor_phone" class="{{ $fieldLabelClasses }}">Telefone</label>
                                <input id="exhibitor_phone" name="phone" type="text" required inputmode="tel" autocomplete="tel" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="(00) 00000-0000" class="{{ $fieldInputClasses }}{{ $errors->has('phone') ? $fieldErrorClasses : '' }}">
                                @error('phone')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="exhibitor_document" class="{{ $fieldLabelClasses }}">CPF/CNPJ</label>
                                <input id="exhibitor_document" name="document" type="text" required inputmode="numeric" value="{{ old('document', auth()->user()->doc ?? '') }}" placeholder="000.000.000-00" class="{{ $fieldInputClasses }}{{ $errors->has('document') ? $fieldErrorClasses : '' }}">
                                @error('document')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company_name" class="{{ $fieldLabelClasses }}">Empresa</label>
                                <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}" class="{{ $fieldInputClasses }}{{ $errors->has('company_name') ? $fieldErrorClasses : '' }}">
                                @error('company_name')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company_document" class="{{ $fieldLabelClasses }}">CNPJ da empresa</label>
                                <input id="company_document" name="company_document" type="text" inputmode="numeric" value="{{ old('company_document') }}" placeholder="00.000.000/0000-00" class="{{ $fieldInputClasses }}{{ $errors->has('company_document') ? $fieldErrorClasses : '' }}">
                                @error('company_document')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="brand_name" class="{{ $fieldLabelClasses }}">Nome da marca</label>
                                <input id="brand_name" name="brand_name" type="text" required value="{{ old('brand_name') }}" class="{{ $fieldInputClasses }}{{ $errors->has('brand_name') ? $fieldErrorClasses : '' }}">
                                @error('brand_name')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="exhibitor_quantity" class="{{ $fieldLabelClasses }}">Quantidade desejada</label>
                                <input id="exhibitor_quantity" name="quantity" type="number" min="1" max="{{ max(1, (int) $remainingSlots) }}" value="{{ old('quantity', 1) }}" data-unit-price="{{ $unitPrice }}" class="{{ $fieldInputClasses }}{{ $errors->has('quantity') ? $fieldErrorClasses : '' }}">
                                @error('quantity')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="exhibitor_description" class="{{ $fieldLabelClasses }}">Descrição resumida</label>
                                <textarea id="exhibitor_description" name="description" rows="4" class="{{ $fieldInputClasses }}{{ $errors->has('description') ? $fieldErrorClasses : '' }}" placeholder="Resumo do que será apresentado no evento">{{ old('description') }}</textarea>
                                @error('description')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        @guest
                            <div class="grid gap-4 border-t border-slate-100 pt-6 md:grid-cols-2">
                                <div>
                                    <label for="password" class="{{ $fieldLabelClasses }}">Senha de acesso</label>
                                    <input id="password" type="password" name="password" required autocomplete="new-password" class="{{ $fieldInputClasses }}{{ $errors->has('password') ? $fieldErrorClasses : '' }}">
                                    @error('password')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="{{ $fieldLabelClasses }}">Confirmar senha</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $fieldInputClasses }}">
                                </div>
                            </div>
                        @endguest

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <label class="flex cursor-pointer items-start gap-3 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="terms" value="1" required class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('terms') ? 'checked' : '' }}>
                                <span>Confirmo que os dados informados são verdadeiros e aceito os termos de participação como expositor deste evento.</span>
                            </label>
                            @error('terms')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest text-blue-700">Total da reserva</p>
                                <p class="mt-1 text-3xl font-black text-blue-950" id="exhibitor-total">{{ 'R$ ' . number_format($unitPrice, 2, ',', '.') }}</p>
                            </div>
                            <button type="submit" class="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-6 py-4 text-lg font-black text-white shadow-lg transition hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60" {{ ($isPaid && count($activeGateways) === 0) || $remainingSlots <= 0 ? 'disabled' : '' }}>
                                <i class="fas fa-credit-card"></i> {{ $isPaid ? 'Ir para pagamento' : 'Confirmar reserva' }}
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const quantity = document.getElementById('exhibitor_quantity');
    const total = document.getElementById('exhibitor-total');

    function digits(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function maskPhone(value) {
        const d = digits(value).slice(0, 11);
        if (d.length <= 10) {
            return d.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, function (_, a, b, c) {
                return [a ? '(' + a + ')' : '', b, c ? '-' + c : ''].join(' ').trim();
            });
        }
        return d.replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, function (_, a, b, c) {
            return [a ? '(' + a + ')' : '', b, c ? '-' + c : ''].join(' ').trim();
        });
    }

    function maskDocument(value) {
        const d = digits(value).slice(0, 14);
        if (d.length <= 11) {
            return d.replace(/^(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2}).*/, function (_, a, b, c, e) {
                return [a, b ? '.' + b : '', c ? '.' + c : '', e ? '-' + e : ''].join('');
            });
        }
        return d.replace(/^(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2}).*/, function (_, a, b, c, e, f) {
            return [a, b ? '.' + b : '', c ? '.' + c : '', e ? '/' + e : '', f ? '-' + f : ''].join('');
        });
    }

    function refreshTotal() {
        if (!quantity || !total) return;
        const unit = Number(quantity.dataset.unitPrice || 0);
        const qty = Math.max(1, Number(quantity.value || 1));
        total.textContent = money.format(unit * qty);
    }

    document.getElementById('exhibitor_phone')?.addEventListener('input', function () {
        this.value = maskPhone(this.value);
    });
    document.getElementById('exhibitor_document')?.addEventListener('input', function () {
        this.value = maskDocument(this.value);
    });
    document.getElementById('company_document')?.addEventListener('input', function () {
        this.value = maskDocument(this.value);
    });
    quantity?.addEventListener('input', refreshTotal);
    refreshTotal();

    document.querySelectorAll('.exhibitor-gateway-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.exhibitor-gateway-card').forEach(function (item) {
                item.classList.remove('border-blue-500', 'bg-blue-50');
                item.classList.add('border-slate-200', 'bg-white');
                item.querySelector('.fa-check-circle')?.classList.add('hidden');
            });
            card.classList.add('border-blue-500', 'bg-blue-50');
            card.classList.remove('border-slate-200', 'bg-white');
            card.querySelector('.fa-check-circle')?.classList.remove('hidden');
            card.querySelector('input[type="radio"]').checked = true;
        });
    });
});
</script>
@endpush
@endsection
