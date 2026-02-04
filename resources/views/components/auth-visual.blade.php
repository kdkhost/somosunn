<div class="hidden md:flex flex-col items-center justify-center gap-6 p-12 bg-gradient-to-br from-[#7a5af8] via-[#6a40e6] to-[#4cc3ff] text-white relative overflow-hidden h-full min-h-[500px]" id="auth-banner-container">
    <canvas id="auth-particles" class="absolute inset-0 w-full h-full pointer-events-none opacity-40"></canvas>
    
    @php
        $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
        $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
    @endphp
    
    <div class="relative z-10 flex flex-col items-center w-full">
        <img src="{{ $logoAuthSrc }}" class="h-16 mb-6 drop-shadow-lg animate-fade-in-up" alt="Logo" onerror="this.style.display='none';">
        
        <h2 class="text-3xl font-bold mb-3 text-center drop-shadow-md animate-fade-in-up" style="animation-delay: 100ms">{{ $title ?? 'Bem-vindo' }}</h2>
        
        <p class="max-w-xs text-center text-blue-50/90 text-sm leading-relaxed animate-fade-in-up" style="animation-delay: 200ms">
            {{ $slot->isEmpty() ? 'Acesse o ecossistema completo de cursos, mentorias e networking.' : $slot }}
        </p>
        
        @if(isset($showSocial) && $showSocial)
        <div class="flex gap-3 mt-8 animate-fade-in-up" style="animation-delay: 300ms">
            @if(\App\Models\Setting::get('social_google_active'))
            <a href="{{ route('social.redirect','google') }}" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="Google">
                <i class="fab fa-google"></i>
            </a>
            @endif
            @if(\App\Models\Setting::get('social_facebook_active'))
            <a href="{{ route('social.redirect','facebook') }}" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            @endif
            @if(\App\Models\Setting::get('social_linkedin_active'))
            <a href="{{ route('social.redirect','linkedin') }}" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
            @endif
        </div>
        @endif
        
        <!-- Decoration icons (Rocket style) -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-white/5 rounded-full blur-2xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-32 h-32 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
    </div>

    <script>
    (function() {
        const canvas = document.getElementById('auth-particles');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        // Configuration
        const particleCount = 60;
        const connectionDistance = 120;
        const speed = 0.4;
        
        function resize() {
            width = canvas.width = canvas.parentElement.offsetWidth;
            height = canvas.height = canvas.parentElement.offsetHeight;
        }
        
        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * speed;
                this.vy = (Math.random() - 0.5) * speed;
                this.size = Math.random() * 2 + 1; // Sized 1-3px
                this.alpha = Math.random() * 0.5 + 0.2;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                
                // Wrap around screen
                if(this.x < 0) this.x = width;
                if(this.x > width) this.x = 0;
                if(this.y < 0) this.y = height;
                if(this.y > height) this.y = 0;
            }
            draw() {
                ctx.fillStyle = `rgba(255,255,255,${this.alpha})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI*2);
                ctx.fill();
            }
        }

        function init() {
            resize();
            particles = [];
            for(let i=0; i<particleCount; i++) particles.push(new Particle());
            animate();
        }
        
        function animate() {
            ctx.clearRect(0,0,width,height);
            
            // Update and draw particles
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            
            // Draw connections
            ctx.lineWidth = 0.5;
            for(let i=0; i<particles.length; i++) {
                for(let j=i+1; j<particles.length; j++) {
                    let p1 = particles[i];
                    let p2 = particles[j];
                    let dx = p1.x - p2.x;
                    let dy = p1.y - p2.y;
                    let dist = Math.sqrt(dx*dx + dy*dy);
                    
                    if(dist < connectionDistance) {
                        // Opacity based on distance
                        let opacity = (1 - dist/connectionDistance) * 0.3;
                        ctx.strokeStyle = `rgba(255,255,255,${opacity})`;
                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();
                    }
                }
            }
            
            requestAnimationFrame(animate);
        }
        
        window.addEventListener('resize', resize);
        // Delay init to ensure container size is set
        setTimeout(init, 100);
    })();
    </script>

    <style>
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>
