# Baixa Shepherd.js, plyr.polyfilled.js e cria estrutura para member-tour.js
$ErrorActionPreference = 'Stop'


$assets = @(
    @{ name = 'shepherd'; version = '8.4.1'; files = @(
        @{ url = 'https://unpkg.com/shepherd.js@8.4.1/dist/js/shepherd.min.js'; path = 'js/shepherd.min.js' },
        @{ url = 'https://unpkg.com/shepherd.js@8.4.1/dist/css/shepherd.css'; path = 'css/shepherd.css' }
    )},
    @{ name = 'plyr'; version = '3.7.8'; files = @(
        @{ url = 'https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.js'; path = 'plyr.polyfilled.js' }
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

# Cria estrutura para member-tour.js (arquivo custom, restauração manual)
$memberTourPath = "public/vendor/member-tour/js"
if (!(Test-Path $memberTourPath)) { New-Item -ItemType Directory -Path $memberTourPath | Out-Null }
Write-Host "Se necessário, coloque o member-tour.js real em $memberTourPath"
Write-Host "Shepherd.js e plyr.polyfilled.js baixados com sucesso."
