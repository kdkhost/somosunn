<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                Fale <span class="text-gradient">Conosco</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            [[CONTACT_ALERTS]]

            <div class="grid lg:grid-cols-2 gap-8 md:gap-12">
                [[CONTACT_INFO]]
                [[CONTACT_FORM]]
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Nossa Localização</h2>
            <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px]">
                <iframe
                    src="[[CONTACT_MAP_EMBED_URL]]"
                    class="w-full h-full border-0"
                    loading="lazy"
                    title="Localização UNN"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    [[FAQ_SECTION]]
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.unn-title-gradient {
    background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
.unn-title-max {
    max-width: 700px;
    word-break: break-word;
    margin-left: auto;
    margin-right: auto;
}
@media (max-width: 640px) {
    .unn-title-max {
        font-size: 2.2rem !important;
        max-width: 95vw;
    }
}
</style>

