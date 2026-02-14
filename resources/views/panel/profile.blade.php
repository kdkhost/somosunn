@extends('panel.member.layout')

@section('title', 'Editar Perfil')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-900">Editar Perfil</h2>
    <form id="profile-form" method="POST" action="{{ route('panel.profile.update') }}" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
            <div class="md:col-span-2">
                <label class="text-sm font-bold text-slate-700">Telefone</label>
                <input id="profile_phone" name="phone" value="{{ old('phone', $user->phone) }}"
                    maxlength="20" inputmode="tel" autocomplete="tel" data-mask-phone
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-bold text-slate-700">CPF/CNPJ</label>
                <input id="profile_doc" name="doc" value="{{ old('doc', $user->doc) }}"
                    maxlength="18" inputmode="numeric" autocomplete="off" data-mask-doc
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div class="md:col-span-1">
                <label class="text-sm font-bold text-slate-700">CEP</label>
                <input id="profile_cep" name="cep" value="{{ old('cep', $user->cep) }}"
                    data-mask-cep maxlength="9" inputmode="numeric" autocomplete="postal-code"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                <p id="profile_cep_feedback" class="mt-2 text-xs text-slate-500"></p>
            </div>
            <div class="md:col-span-3">
                <label class="text-sm font-bold text-slate-700">Rua/Avenida</label>
                <input id="profile_street" name="street" value="{{ old('street', $user->street) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-bold text-slate-700">Bairro</label>
                <input id="profile_neighborhood" name="neighborhood" value="{{ old('neighborhood', $user->neighborhood) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Número</label>
                <input id="profile_number" name="number" value="{{ old('number', $user->number) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Complemento</label>
                <input id="profile_complement" name="complement" value="{{ old('complement', $user->complement) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-bold text-slate-700">Cidade</label>
                <input id="profile_city" name="city" value="{{ old('city', $user->city) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Estado</label>
                <input id="profile_state" name="state" value="{{ old('state', $user->state) }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
            </div>
        </div>
        <div class="mt-8 text-right">
            <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-bold">Salvar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
<script>
function initMasks() {
    if (window.Inputmask) {
        var phone = document.getElementById('profile_phone');
        if (phone) {
            Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(phone);
        }
        var doc = document.getElementById('profile_doc');
        if (doc) {
            Inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true }).mask(doc);
        }
    }
}
function initViaCepAutofill() {
    var cepInput = document.getElementById('profile_cep');
    if (!cepInput) return;
    var feedback = document.getElementById('profile_cep_feedback');
    var streetInput = document.getElementById('profile_street');
    var neighborhoodInput = document.getElementById('profile_neighborhood');
    var cityInput = document.getElementById('profile_city');
    var stateInput = document.getElementById('profile_state');
    var lastCep = '';
    function setFeedback(message, isError) {
        if (!feedback) return;
        feedback.textContent = message || '';
        feedback.classList.toggle('text-red-600', !!isError);
        feedback.classList.toggle('text-slate-500', !isError);
    }
    function fetchCep() {
        var digits = (cepInput.value || '').replace(/\D/g, '');
        if (digits.length !== 8 || digits === lastCep) return;
        lastCep = digits;
        setFeedback('Buscando endereço...', false);
        fetch('https://viacep.com.br/ws/' + digits + '/json/')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data || data.erro) {
                    setFeedback('CEP não encontrado. Complete o endereço manualmente.', true);
                    return;
                }
                if (streetInput && !streetInput.value) streetInput.value = data.logradouro || '';
                if (neighborhoodInput && !neighborhoodInput.value) neighborhoodInput.value = data.bairro || '';
                if (cityInput && !cityInput.value) cityInput.value = data.localidade || '';
                if (stateInput && !stateInput.value) stateInput.value = (data.uf || '').toUpperCase();
                setFeedback('Endereço preenchido automaticamente.', false);
            })
            .catch(function () {
                setFeedback('Não foi possível consultar o CEP agora. Complete manualmente.', true);
            });
    }
    cepInput.addEventListener('blur', fetchCep);
    cepInput.addEventListener('input', function () {
        if ((cepInput.value || '').replace(/\D/g, '').length < 8) lastCep = '';
    });
}
document.addEventListener('DOMContentLoaded', function () {
    if (window.Inputmask) {
        Inputmask('99999-999').mask(document.getElementById('profile_cep'));
    }
    initMasks();
    initViaCepAutofill();
});
</script>
@endpush
