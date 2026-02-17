#!/bin/bash
# Script para baixar FilePond e plugins localmente

echo "📦 Baixando FilePond e plugins..."

# Criar diretório
mkdir -p public/vendor/filepond/plugins

cd public/vendor/filepond

# FilePond core
echo "⬇️ FilePond core..."
curl -o filepond.css https://unpkg.com/filepond/dist/filepond.css
curl -o filepond.js https://unpkg.com/filepond/dist/filepond.js

# Image Preview Plugin
echo "⬇️ Image Preview Plugin..."
curl -o plugins/filepond-plugin-image-preview.css https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css
curl -o plugins/filepond-plugin-image-preview.js https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js

# File Validate Size Plugin
echo "⬇️ File Validate Size Plugin..."
curl -o plugins/filepond-plugin-file-validate-size.css https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.css
curl -o plugins/filepond-plugin-file-validate-size.js https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js

# File Validate Type Plugin
echo "⬇️ File Validate Type Plugin..."
curl -o plugins/filepond-plugin-file-validate-type.css https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.css
curl -o plugins/filepond-plugin-file-validate-type.js https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js

echo "✅ Download concluído!"
echo "📁 Arquivos salvos em: public/vendor/filepond/"
