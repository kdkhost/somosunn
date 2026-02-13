# Script PowerShell para baixar e organizar assets JS/CSS reais em public/vendor/
$ErrorActionPreference = 'Stop'

$assets = @(
    @{ name = 'fontawesome-free'; version = '6.5.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css'; path = 'css/all.min.css' }
    )},
    @{ name = 'overlayscrollbars'; version = '1.13.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/css/OverlayScrollbars.min.css'; path = 'css/OverlayScrollbars.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/js/jquery.overlayScrollbars.min.js'; path = 'js/jquery.overlayScrollbars.min.js' }
    )},
    @{ name = 'jqvmap'; version = '1.5.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css'; path = 'css/jqvmap.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js'; path = 'js/jquery.vmap.min.js' },
        @{ url = 'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.world.js'; path = 'js/maps/jquery.vmap.world.js' }
    )},
    @{ name = 'bootstrap-colorpicker'; version = '3.4.0'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/css/bootstrap-colorpicker.min.css'; path = 'css/bootstrap-colorpicker.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js'; path = 'js/bootstrap-colorpicker.min.js' }
    )},
    @{ name = 'cropperjs'; version = '1.6.2'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css'; path = 'css/cropper.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js'; path = 'js/cropper.min.js' }
    )},
    @{ name = 'summernote'; version = '0.8.20'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css'; path = 'css/summernote-bs4.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js'; path = 'js/summernote-bs4.min.js' }
    )},
    @{ name = 'toastr'; version = '2.1.4'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css'; path = 'css/toastr.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js'; path = 'js/toastr.min.js' }
    )},
    @{ name = 'sweetalert2'; version = '11.10.0'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css'; path = 'css/sweetalert2.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js'; path = 'js/sweetalert2.all.min.js' }
    )},
    @{ name = 'flatpickr'; version = '4.6.13'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css'; path = 'css/flatpickr.min.css' },
        @{ url = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js'; path = 'js/flatpickr.min.js' },
        @{ url = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/pt.js'; path = 'js/l10n/pt.js' }
    )},
    @{ name = 'jquery'; version = '3.7.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js'; path = 'jquery.min.js' }
    )},
    @{ name = 'bootstrap'; version = '4.6.2'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'; path = 'js/bootstrap.bundle.min.js' }
    )},
    @{ name = 'inputmask'; version = '5.0.8'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js'; path = 'js/jquery.inputmask.min.js' }
    )},
    @{ name = 'chart.js'; version = '3.9.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js'; path = 'js/chart.min.js' }
    )},
    @{ name = 'jquery-knob-chif'; version = '1.2.13'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/jquery-knob-chif@1.2.13/dist/jquery.knob.min.js'; path = 'js/jquery.knob.min.js' }
    )},
    @{ name = 'pjax'; version = '2.0.1'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js'; path = 'js/jquery.pjax.min.js' }
    )}
)

foreach ($asset in $assets) {
    $base = "public/vendor/" + $asset.name
    if (!(Test-Path $base)) { New-Item -ItemType Directory -Path $base | Out-Null }
    foreach ($file in $asset.files) {
        $dest = Join-Path $base $file.path
        $destDir = Split-Path $dest -Parent
        if (!(Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir | Out-Null }
        Write-Host "Baixando $($file.url) para $dest ..."
        Invoke-WebRequest -Uri $file.url -OutFile $dest
    }
}
Write-Host "Todos os assets foram baixados e organizados em public/vendor/"
