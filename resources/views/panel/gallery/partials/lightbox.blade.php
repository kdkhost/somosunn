<div id="gallery-lightbox" class="fixed inset-0 z-[95] hidden">
    <div data-gallery-close-lightbox class="absolute inset-0 bg-slate-950/90 backdrop-blur-md"></div>

    <div class="relative flex min-h-full items-center justify-center py-16 sm:p-4 md:p-8">
        <button type="button"
            data-gallery-close-lightbox
            class="absolute right-3 top-3 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20 sm:right-4 sm:top-4 sm:h-12 sm:w-12 md:right-8 md:top-8">
            <i class="fas fa-xmark text-lg"></i>
        </button>

        <figure class="lightbox-image relative w-full max-w-6xl overflow-hidden sm:rounded-[2.2rem] sm:border border-white/10 bg-transparent sm:bg-slate-950/70 sm:p-3 sm:shadow-[0_30px_90px_rgba(15,23,42,0.55)]">
            <img id="gallery-lightbox-image" src="" alt="" class="max-h-[82vh] w-full rounded-none sm:rounded-[1.7rem] object-contain">
            <figcaption id="gallery-lightbox-title" class="px-4 sm:px-3 pb-1 pt-4 text-center text-sm font-bold tracking-wide text-slate-200"></figcaption>
        </figure>
    </div>
</div>
