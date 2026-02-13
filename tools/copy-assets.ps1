# PowerShell script para copiar assets JS/CSS do node_modules para public/vendor

$ErrorActionPreference = 'Stop'

$assets = @(
    @{ src = 'node_modules/jquery/dist/jquery.min.js'; dest = 'public/vendor/jquery/jquery.min.js' },
    @{ src = 'node_modules/@fortawesome/fontawesome-free/css/all.min.css'; dest = 'public/vendor/fontawesome-free/css/all.min.css' },
    @{ src = 'node_modules/@fortawesome/fontawesome-free/webfonts'; dest = 'public/vendor/fontawesome-free/webfonts' },
    @{ src = 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'; dest = 'public/vendor/bootstrap/js/bootstrap.bundle.min.js' },
    @{ src = 'node_modules/bootstrap/dist/css/bootstrap.min.css'; dest = 'public/vendor/bootstrap/css/bootstrap.min.css' },
    @{ src = 'node_modules/admin-lte/dist/js/adminlte.min.js'; dest = 'public/vendor/admin-lte/js/adminlte.min.js' },
    @{ src = 'node_modules/admin-lte/dist/css/adminlte.min.css'; dest = 'public/vendor/admin-lte/css/adminlte.min.css' },
    @{ src = 'node_modules/overlayscrollbars/js/OverlayScrollbars.min.js'; dest = 'public/vendor/overlayscrollbars/js/jquery.overlayScrollbars.min.js' },
    @{ src = 'node_modules/overlayscrollbars/css/OverlayScrollbars.min.css'; dest = 'public/vendor/overlayscrollbars/css/OverlayScrollbars.min.css' },
    @{ src = 'node_modules/chart.js/dist/chart.min.js'; dest = 'public/vendor/chart.js/chart.min.js' },
    @{ src = 'node_modules/jqvmap/dist/jquery.vmap.min.js'; dest = 'public/vendor/jqvmap/jquery.vmap.min.js' },
    @{ src = 'node_modules/jqvmap/dist/maps/jquery.vmap.world.js'; dest = 'public/vendor/jqvmap/maps/jquery.vmap.world.js' },
    @{ src = 'node_modules/cropperjs/dist/cropper.min.js'; dest = 'public/vendor/cropperjs/cropper.min.js' },
    @{ src = 'node_modules/cropperjs/dist/cropper.min.css'; dest = 'public/vendor/cropperjs/cropper.min.css' },
    @{ src = 'node_modules/summernote/dist/summernote-bs4.min.js'; dest = 'public/vendor/summernote/summernote-bs4.min.js' },
    @{ src = 'node_modules/summernote/dist/summernote-bs4.min.css'; dest = 'public/vendor/summernote/summernote-bs4.min.css' },
    @{ src = 'node_modules/jquery-pjax/jquery.pjax.js'; dest = 'public/vendor/jquery-pjax/jquery.pjax.min.js' },
    @{ src = 'node_modules/toastr/build/toastr.min.js'; dest = 'public/vendor/toastr/toastr.min.js' },
    @{ src = 'node_modules/toastr/build/toastr.min.css'; dest = 'public/vendor/toastr/toastr.min.css' },
    @{ src = 'node_modules/sweetalert2/dist/sweetalert2.all.min.js'; dest = 'public/vendor/sweetalert2/sweetalert2.all.min.js' },
    @{ src = 'node_modules/sweetalert2/dist/sweetalert2.min.css'; dest = 'public/vendor/sweetalert2/sweetalert2.min.css' },
    @{ src = 'node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js'; dest = 'public/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.js' },
    @{ src = 'node_modules/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css'; dest = 'public/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css' },
    @{ src = 'node_modules/flatpickr/dist/flatpickr.min.css'; dest = 'public/vendor/flatpickr/flatpickr.min.css' }
)

foreach ($asset in $assets) {
    $src = $asset.src
    $dest = $asset.dest
    $destDir = Split-Path $dest -Parent
    if (!(Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }
    if (Test-Path $src) {
        if ((Get-Item $src).PSIsContainer) {
            Copy-Item $src $dest -Recurse -Force
        } else {
            Copy-Item $src $dest -Force
        }
        Write-Host "Copiado: $src -> $dest"
    } else {
        Write-Warning "Arquivo não encontrado: $src"
    }
}

# Baixar manualmente o jquery-knob se não existir
$knobDest = 'public/vendor/jquery-knob-chif/jquery.knob.min.js'
$knobDir = Split-Path $knobDest -Parent
if (!(Test-Path $knobDir)) { New-Item -ItemType Directory -Path $knobDir -Force | Out-Null }
if (!(Test-Path $knobDest)) {
    $url = 'https://cdn.jsdelivr.net/npm/jquery-knob-chif@1.2.13/dist/jquery.knob.min.js'
    Write-Host "Baixando jquery-knob: $url"
    Invoke-WebRequest -Uri $url -OutFile $knobDest
}