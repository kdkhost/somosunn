<div>
    <h2 class="text-3xl font-black text-gray-900 mb-8">Informações de Contato</h2>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-envelope text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">E-mail</h3>
                <p class="text-gray-600">{{ $companyEmail }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fab fa-whatsapp text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                <p class="text-gray-600">{{ $companyPhone }}</p>
                <p class="text-sm text-gray-500">Seg-Sex, 9h às 18h</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-map-marker-alt text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">Endereço</h3>
                <p class="text-gray-600">
                    {{ $companyAddress }}{{ $companyNumber ? ', '.$companyNumber : '' }}@if($companyComplement) - {{ $companyComplement }}@endif
                </p>
                <p class="text-gray-600">{{ $companyDistrict }}, {{ $companyCity }} - {{ $companyState }}</p>
                <p class="text-gray-600">CEP: {{ $companyZip }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-2xl p-6 shadow-lg text-center md:text-left">
        @if(!empty($socialLinks))
            <h3 class="font-bold text-gray-900 mb-4">Redes Sociais</h3>
            <div class="flex gap-4 justify-center md:justify-start flex-wrap">
                @foreach($socialLinks as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                       class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition"
                       aria-label="{{ $link['title'] }}">
                        <i class="{{ $link['icon'] }} text-xl"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

