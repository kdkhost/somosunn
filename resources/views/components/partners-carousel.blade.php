@php
    $carouselPartners = collect();
    $supportsCoupons = false;

    try {
        if (\App\Models\Partner::tableExists()) {
            $supportsCoupons = \App\Models\Partner::couponsTableExists();
            $carouselPartnersQuery = \App\Models\Partner::active();

            if ($supportsCoupons) {
                $carouselPartnersQuery->withCount('activeCoupons');
            }

            $carouselPartners = $carouselPartnersQuery->get();
        }
    } catch (\Throwable $e) {
        $carouselPartners = collect();
        $supportsCoupons = false;
    }
@endphp

@if($carouselPartners->isNotEmpty())
    <section class="partners-carousel-section py-8" aria-label="Empresas Parceiras">
        <div class="partners-carousel-header text-center mb-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-2"
                style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1d4ed8;">
                <i class="fas fa-handshake"></i> Nossos Parceiros
            </div>
            <p class="text-slate-500 text-sm mt-1">Benefícios exclusivos para membros da plataforma</p>
        </div>

        <div class="partners-track-wrapper" id="partnersTrack">
            <div class="partners-track" id="partnersInner">
                {{-- Duplicamos para loop infinito --}}
                @foreach([$carouselPartners, $carouselPartners] as $set)
                    @foreach($set as $p)
                        <a href="{{ route('partners.show', $p->slug) }}" class="partner-logo-card" aria-label="{{ $p->name }}">
                            <div class="partner-logo-inner">
                                @if($p->logo_url)
                                    <img src="{{ $p->logo_url }}" alt="{{ $p->name }}" loading="lazy">
                                @else
                                    <div class="partner-logo-placeholder">
                                        <i class="fas fa-building"></i>
                                        <span>{{ $p->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="partner-logo-tooltip">
                                <span>{{ $p->name }}</span>
                                @if($supportsCoupons && (int) ($p->active_coupons_count ?? 0) > 0)
                                    <span class="partner-coupon-badge">
                                        <i class="fas fa-ticket-alt"></i> {{ (int) $p->active_coupons_count }} cupom(s)
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('partners.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:scale-105"
                style="background: linear-gradient(135deg, #1f5edb, #177fd6); color: #fff; box-shadow: 0 4px 15px rgba(31,94,219,0.3);">
                <i class="fas fa-handshake"></i> Ver todos os parceiros
            </a>
        </div>
    </section>

    <style>
        /* ── Partners Carousel ──────────────────────────────────────────────── */
        .partners-carousel-section {
            padding: 2rem 0;
        }

        .partners-track-wrapper {
            overflow: visible;
            /* ← Permite que tooltips (position:absolute) apareçam acima */
            position: relative;
            width: 100%;
            padding-top: 60px;
            /* ← espaço acima para o tooltip não ser cortado */
            margin-top: -60px;
            /* ← compensar o espaço extra sem quebrar o layout */
            cursor: grab;
            user-select: none;
        }

        .partners-track-wrapper:active {
            cursor: grabbing;
        }

        .partners-track {
            display: flex;
            gap: 1.25rem;
            width: max-content;
            animation: partners-scroll 28s linear infinite;
            padding: 0.75rem 0;
        }

        .partners-track.paused {
            animation-play-state: paused;
        }

        @keyframes partners-scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .partner-logo-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none !important;
            flex-shrink: 0;
        }

        .partner-logo-inner {
            width: 160px;
            height: 80px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 10px 14px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .partner-logo-card:hover .partner-logo-inner {
            transform: scale(1.07);
            box-shadow: 0 6px 20px rgba(31, 94, 219, 0.18);
            border-color: #93c5fd;
        }

        .partner-logo-inner img {
            max-width: 136px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            filter: grayscale(20%);
            transition: filter 0.25s ease;
        }

        .partner-logo-card:hover .partner-logo-inner img {
            filter: grayscale(0%);
        }

        .partner-logo-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #94a3b8;
            font-size: 0.7rem;
            gap: 4px;
            text-align: center;
        }

        .partner-logo-placeholder i {
            font-size: 1.4rem;
        }

        /* Tooltip */
        .partner-logo-tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(6px);
            background: #1e293b;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            z-index: 10;
        }

        .partner-logo-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #1e293b;
        }

        .partner-logo-card:hover .partner-logo-tooltip {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .partner-coupon-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: linear-gradient(135deg, #1f5edb, #177fd6);
            border-radius: 20px;
            padding: 1px 8px;
            font-size: 0.65rem;
            font-weight: 700;
            color: #fff;
        }
    </style>

    <script>
        (function () {
            const wrapper = document.getElementById('partnersTrack');
            const track = document.getElementById('partnersInner');
            if (!wrapper || !track) return;

            // Pause on hover
            wrapper.addEventListener('mouseenter', () => track.classList.add('paused'));
            wrapper.addEventListener('mouseleave', () => {
                if (!isDragging) track.classList.remove('paused');
            });

            // Drag to scroll
            let isDragging = false, startX = 0, scrollLeft = 0;

            function onDown(e) {
                isDragging = true;
                track.classList.add('paused');
                startX = (e.touches ? e.touches[0].clientX : e.clientX) - wrapper.getBoundingClientRect().left;
                scrollLeft = wrapper.scrollLeft;
                wrapper.style.cursor = 'grabbing';
            }
            function onMove(e) {
                if (!isDragging) return;
                e.preventDefault();
                const x = (e.touches ? e.touches[0].clientX : e.clientX) - wrapper.getBoundingClientRect().left;
                wrapper.scrollLeft = scrollLeft - (x - startX);
            }
            function onUp() {
                isDragging = false;
                wrapper.style.cursor = 'grab';
                track.classList.remove('paused');
            }

            wrapper.addEventListener('mousedown', onDown);
            wrapper.addEventListener('mousemove', onMove);
            wrapper.addEventListener('mouseup', onUp);
            wrapper.addEventListener('mouseleave', onUp);
            wrapper.addEventListener('touchstart', onDown, { passive: true });
            wrapper.addEventListener('touchmove', onMove, { passive: false });
            wrapper.addEventListener('touchend', onUp);
        })();
    </script>
@endif
