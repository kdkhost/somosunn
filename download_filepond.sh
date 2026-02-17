#!/bin/bash
# Script para baixar FilePond e plugins localmente com verificação

set -e  # Parar em caso de erro

echo "📦 Baixando FilePond e plugins..."

# Criar diretórios
mkdir -p public/vendor/filepond/plugins

cd public/vendor/filepond

# Função para baixar e verificar
download_file() {
    local url=$1
    local output=$2
    echo "⬇️  Baixando: $output"
    
    if command -v wget &> /dev/null; then
        wget -q --show-progress -O "$output" "$url"
    elif command -v curl &> /dev/null; then
        curl -L -o "$output" "$url"
    else
        echo "❌ Erro: wget ou curl não encontrado!"
        exit 1
    fi
    
    # Verificar se o arquivo foi baixado e não está vazio
    if [ ! -s "$output" ]; then
        echo "❌ Erro ao baixar: $output"
        exit 1
    fi
    
    # Verificar se não é uma página de erro HTML
    if file "$output" | grep -q "HTML"; then
        echo "❌ Erro: $output contém HTML em vez do arquivo esperado"
        exit 1
    fi
    
    echo "✅ OK: $output"
}

# FilePond core
download_file "https://unpkg.com/filepond@4.30.4/dist/filepond.css" "filepond.css"
download_file "https://unpkg.com/filepond@4.30.4/dist/filepond.js" "filepond.js"

# Image Preview Plugin
download_file "https://unpkg.com/filepond-plugin-image-preview@4.6.11/dist/filepond-plugin-image-preview.css" "plugins/filepond-plugin-image-preview.css"
download_file "https://unpkg.com/filepond-plugin-image-preview@4.6.11/dist/filepond-plugin-image-preview.js" "plugins/filepond-plugin-image-preview.js"

# File Validate Size Plugin
download_file "https://unpkg.com/filepond-plugin-file-validate-size@2.2.7/dist/filepond-plugin-file-validate-size.css" "plugins/filepond-plugin-file-validate-size.css"
download_file "https://unpkg.com/filepond-plugin-file-validate-size@2.2.7/dist/filepond-plugin-file-validate-size.js" "plugins/filepond-plugin-file-validate-size.js"

# File Validate Type Plugin
download_file "https://unpkg.com/filepond-plugin-file-validate-type@1.2.8/dist/filepond-plugin-file-validate-type.css" "plugins/filepond-plugin-file-validate-type.css"
download_file "https://unpkg.com/filepond-plugin-file-validate-type@1.2.8/dist/filepond-plugin-file-validate-type.js" "plugins/filepond-plugin-file-validate-type.js"

echo ""
echo "✅ Download concluído com sucesso!"
echo "📁 Arquivos salvos em: public/vendor/filepond/"
echo ""
echo "📊 Resumo:"
ls -lh filepond.* plugins/*.* | awk '{print $9, "-", $5}'
