<div id="gallery-lightbox" class="fixed inset-0 z-[95] hidden">
    <div data-gallery-close-lightbox class="absolute inset-0 bg-slate-950/90 backdrop-blur-md"></div>

    <div class="relative flex min-h-full items-center justify-center p-4 md:p-8">
        <button type="button"
            data-gallery-close-lightbox
            class="absolute right-4 top-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:right-8 md:top-8">
            <i class="fas fa-xmark text-lg"></i>
        </button>

        <figure class="lightbox-image relative w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/60 p-3 shadow-[0_24px_80px_rgba(15,23,42,0.55)]">
            <img id="gallery-lightbox-image" src="" alt="" class="max-h-[82vh] w-full rounded-[1.4rem] object-contain">
            <figcaption id="gallery-lightbox-title" class="px-3 pb-1 pt-4 text-center text-sm font-bold tracking-wide text-slate-200"></figcaption>
        </figure>
    </div>
</div>
