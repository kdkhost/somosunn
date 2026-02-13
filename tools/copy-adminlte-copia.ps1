# Script para automatizar a cópia do AdminLTE para public/vendor/adminlte-copia
$src = "public/vendor/admin-lte"
$dst = "public/vendor/adminlte-copia"

if (!(Test-Path $dst)) {
    New-Item -ItemType Directory -Path $dst | Out-Null
}

$folders = @("css", "js")
foreach ($folder in $folders) {
    $srcFolder = Join-Path $src $folder
    $dstFolder = Join-Path $dst $folder
    if (!(Test-Path $dstFolder)) {
        New-Item -ItemType Directory -Path $dstFolder | Out-Null
    }
    Get-ChildItem -Path $srcFolder -File | ForEach-Object {
        Copy-Item $_.FullName -Destination $dstFolder -Force
    }
}
Write-Host "AdminLTE copiado para $dst com sucesso."
