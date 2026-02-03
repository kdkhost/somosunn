@extends('layouts.app')

@section('title', 'Contato - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-24 pb-12 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-5xl lg:text-6xl font-black leading-tight mb-6">
                Fale <span class="text-gradient">Conosco</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Contact Info -->
                <div>
                    <h2 class="text-3xl font-black text-gray-900 mb-8">Informações de Contato</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl p-6 shadow-lg flex items-start gap-4">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">E-mail</h3>
                                <p class="text-gray-600">contato@somosunn.com.br</p>
                                <p class="text-gray-600">suporte@somosunn.com.br</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-lg flex items-start gap-4">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fab fa-whatsapp text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                                <p class="text-gray-600">(11) 99999-9999</p>
                                <p class="text-sm text-gray-500">Seg-Sex, 9h às 18h</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-lg flex items-start gap-4">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-map-marker-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Endereço</h3>
                                <p class="text-gray-600">Av. Paulista, 1000 - Sala 1001</p>
                                <p class="text-gray-600">Bela Vista, São Paulo - SP</p>
                                <p class="text-gray-600">CEP: 01310-100</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="mt-8">
                        <h3 class="font-bold text-gray-900 mb-4">Redes Sociais</h3>
                        <div class="flex gap-4">
                            <a href="#" class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition">
                                <i class="fab fa-linkedin text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition">
                                <i class="fab fa-youtube text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition">
                                <i class="fab fa-facebook text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-3xl p-8 shadow-2xl">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">Envie uma mensagem</h2>
                    
                    <form action="#" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nome completo</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition" 
                                   style="--tw-ring-color: var(--unn-azul-1)"
                                   placeholder="Seu nome">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition" 
                                   style="--tw-ring-color: var(--unn-azul-1)"
                                   placeholder="seu@email.com">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                            <input type="tel" name="phone" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition" 
                                   style="--tw-ring-color: var(--unn-azul-1)"
                                   placeholder="(00) 00000-0000">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Assunto</label>
                            <select name="subject" required 
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                                    style="--tw-ring-color: var(--unn-azul-1)">
                                <option value="">Selecione um assunto</option>
                                <option value="duvidas">Dúvidas sobre a plataforma</option>
                                <option value="parcerias">Propostas de parceria</option>
                                <option value="suporte">Suporte técnico</option>
                                <option value="comercial">Departamento comercial</option>
                                <option value="imprensa">Assessoria de imprensa</option>
                                <option value="outro">Outro assunto</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mensagem</label>
                            <textarea name="message" rows="5" required 
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition resize-none" 
                                      style="--tw-ring-color: var(--unn-azul-1)"
                                      placeholder="Como podemos ajudar?"></textarea>
                        </div>

                        <button type="submit" class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Nossa Localização</h2>
            <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px]">
                <iframe 
                    src="https://www.openstreetmap.org/export/embed.html?bbox=-46.6600,-23.5650,-46.6500,-23.5550&layer=mapnik&marker=-23.5600,-46.6550"
                    class="w-full h-full border-0"
                    loading="lazy"
                    title="Localização UNN"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Preview -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Perguntas Frequentes</h2>
            
            <div class="space-y-4">
                @php
                    $faqs = [
                        ['q' => 'Como faço para me tornar membro?', 'a' => 'Basta se cadastrar no site. O plano básico é gratuito e já dá acesso à comunidade.'],
                        ['q' => 'Os eventos são presenciais ou online?', 'a' => 'Realizamos eventos nos dois formatos! Temos encontros presenciais em diversas cidades e webinars semanais.'],
                        ['q' => 'Posso cancelar minha assinatura a qualquer momento?', 'a' => 'Sim! Não temos fidelidade. Você pode cancelar quando quiser sem taxas adicionais.'],
                        ['q' => 'Como funciona o sistema de indicações?', 'a' => 'Membros podem indicar outros membros para oportunidades de negócio. Facilitamos e acompanhamos cada conexão.'],
                    ];
                @endphp
                
                @foreach($faqs as $faq)
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h3 class="font-bold text-gray-900 mb-2">{{ $faq['q'] }}</h3>
                    <p class="text-gray-600">{{ $faq['a'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>
@endsection
