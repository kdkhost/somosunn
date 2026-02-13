<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'title' => 'Bem-vindo',
    'showSocial' => false,
    'context' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'title' => 'Bem-vindo',
    'showSocial' => false,
    'context' => null,
]); ?>
<?php foreach (array_filter(([
    'title' => 'Bem-vindo',
    'showSocial' => false,
    'context' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $context = trim((string) ($context ?? ''));

    $globalAnimationEnabled = (string) \App\Models\Setting::get('auth_visual_animation_enabled', '1') === '1';
    $pageAnimationEnabled = $context !== ''
        ? ((string) \App\Models\Setting::get('auth_visual_animation_' . $context, '1') === '1')
        : true;

    $shouldAnimate = $globalAnimationEnabled && $pageAnimationEnabled;

    $animClass = $shouldAnimate ? 'animate-fade-in-up' : '';
    $pulseClass = $shouldAnimate ? 'animate-pulse' : '';

    $googleEnabled = (string) \App\Models\Setting::get('social_google_enabled', \App\Models\Setting::get('social_google_active', '0')) === '1';
    $facebookEnabled = (string) \App\Models\Setting::get('social_facebook_enabled', \App\Models\Setting::get('social_facebook_active', '0')) === '1';
    $linkedinEnabled = (string) \App\Models\Setting::get('social_linkedin_enabled', \App\Models\Setting::get('social_linkedin_active', '0')) === '1';
?>

<div class="hidden md:flex flex-col items-center justify-center gap-6 p-12 bg-gradient-to-br from-[#7a5af8] via-[#6a40e6] to-[#4cc3ff] text-white relative overflow-hidden h-full min-h-[500px]"
    id="auth-banner-container">
    <?php if($shouldAnimate): ?>
        <canvas id="auth-particles" class="absolute inset-0 w-full h-full pointer-events-none opacity-40"></canvas>
    <?php endif; ?>
    
    <?php
        $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
        $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
    ?>
    
    <div class="relative z-10 flex flex-col items-center w-full">
        <img src="<?php echo e($logoAuthSrc); ?>" class="h-24 lg:h-28 w-auto max-w-[260px] mb-6 drop-shadow-lg <?php echo e($animClass); ?>" alt="Logo"
            onerror="this.style.display='none';">
        
        <h2 class="text-3xl font-bold mb-3 text-center drop-shadow-md <?php echo e($animClass); ?>"
            <?php if($shouldAnimate): ?> style="animation-delay: 100ms" <?php endif; ?>>
            <?php echo e($title ?? 'Bem-vindo'); ?>

        </h2>
        
        <p class="max-w-xs text-center text-blue-50/90 text-sm leading-relaxed <?php echo e($animClass); ?>"
            <?php if($shouldAnimate): ?> style="animation-delay: 200ms" <?php endif; ?>>
            <?php echo e($slot->isEmpty() ? 'Acesse o ecossistema completo de cursos, mentorias e networking.' : $slot); ?>

        </p>
        
        <?php if(isset($showSocial) && $showSocial): ?>
        <div class="flex gap-3 mt-8 <?php echo e($animClass); ?>" <?php if($shouldAnimate): ?> style="animation-delay: 300ms" <?php endif; ?>>
            <?php if($googleEnabled): ?>
            <a href="<?php echo e(route('social.redirect', ['provider' => 'google'])); ?>" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="Google">
                <i class="fab fa-google"></i>
            </a>
            <?php endif; ?>
            <?php if($facebookEnabled): ?>
            <a href="<?php echo e(route('social.redirect', ['provider' => 'facebook'])); ?>" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>
            <?php if($linkedinEnabled): ?>
            <a href="<?php echo e(route('social.redirect', ['provider' => 'linkedin'])); ?>" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center backdrop-blur-sm transition-all hover:scale-110" title="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Decoration icons (Rocket style) -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-white/5 rounded-full blur-2xl <?php echo e($pulseClass); ?>"></div>
        <div class="absolute bottom-20 right-10 w-32 h-32 bg-purple-500/20 rounded-full blur-3xl <?php echo e($pulseClass); ?>"
            <?php if($shouldAnimate): ?> style="animation-delay: 1s" <?php endif; ?>></div>
    </div>

    <?php if($shouldAnimate): ?>
        <script>
            (function() {
                const canvas = document.getElementById('auth-particles');
                if (!canvas) return;

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
                        if (this.x < 0) this.x = width;
                        if (this.x > width) this.x = 0;
                        if (this.y < 0) this.y = height;
                        if (this.y > height) this.y = 0;
                    }

                    draw() {
                        ctx.fillStyle = `rgba(255,255,255,${this.alpha})`;
                        ctx.beginPath();
                        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }

                function init() {
                    resize();
                    particles = [];
                    for (let i = 0; i < particleCount; i++) particles.push(new Particle());
                    animate();
                }

                function animate() {
                    ctx.clearRect(0, 0, width, height);

                    // Update and draw particles
                    particles.forEach(p => {
                        p.update();
                        p.draw();
                    });

                    // Draw connections
                    ctx.lineWidth = 0.5;
                    for (let i = 0; i < particles.length; i++) {
                        for (let j = i + 1; j < particles.length; j++) {
                            let p1 = particles[i];
                            let p2 = particles[j];
                            let dx = p1.x - p2.x;
                            let dy = p1.y - p2.y;
                            let dist = Math.sqrt(dx * dx + dy * dy);

                            if (dist < connectionDistance) {
                                // Opacity based on distance
                                let opacity = (1 - dist / connectionDistance) * 0.3;
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
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    <?php endif; ?>
</div>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\components\auth-visual.blade.php ENDPATH**/ ?>