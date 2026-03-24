<div id="gallery-lightbox" class="fixed inset-0 z-[95] flex hidden items-center justify-center bg-black opacity-0 transition-opacity duration-300">

    <div id="gallery-track" class="absolute inset-0 flex items-center justify-center transition-transform duration-300 ease-out">
        <img id="gallery-lightbox-image" src="" alt="" class="max-h-[100dvh] max-w-[100vw] object-contain">
        <video id="gallery-lightbox-video" src="" class="hidden max-h-[100dvh] max-w-[100vw] object-contain" controls playsinline preload="metadata"></video>
    </div>

    <div id="gallery-ui-top" class="absolute left-0 right-0 top-0 z-50 flex items-center justify-between bg-gradient-to-b from-black/80 to-transparent px-4 py-4 transition-opacity duration-300 sm:px-6">
        <div></div>
        <button type="button" data-gallery-close-lightbox
            class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/25">
            <i class="fas fa-xmark text-xl"></i>
        </button>
    </div>

    <div id="gallery-ui-bottom" class="absolute bottom-0 left-0 right-0 z-50 bg-gradient-to-t from-black/90 via-black/60 to-transparent px-4 pb-8 pt-12 transition-opacity duration-300 sm:px-6">
        <div class="mx-auto max-w-6xl">
            <p id="gallery-lightbox-title" class="text-center text-sm font-medium text-white/90 sm:text-left"></p>
        </div>
    </div>
</div>
