#!/bin/bash

echo "======================================"
echo "DIAGNÓSTICO: Upload de Foto - Perfil"
echo "======================================"
echo ""

# 1. Verificar estrutura do banco
echo "1. Verificando estrutura da tabela users..."
php artisan tinker --execute="
\$columns = \DB::select('SHOW COLUMNS FROM users');
\$hasPhoto = false;
foreach (\$columns as \$col) {
    if (\$col->Field === 'photo') {
        \$hasPhoto = true;
        echo '✅ Campo photo EXISTE na tabela users\n';
        echo 'Tipo: ' . \$col->Type . '\n';
        echo 'Null: ' . \$col->Null . '\n';
        break;
    }
}
if (!\$hasPhoto) {
    echo '❌ Campo photo NÃO EXISTE na tabela users\n';
    echo '⚠️  Execute: php artisan migrate\n';
}
"

echo ""

# 2. Verificar diretórios de storage
echo "2. Verificando diretórios de storage..."

if [ -d "storage/app/public/uploads/avatars" ]; then
    echo "✅ Diretório storage/app/public/uploads/avatars existe"
    echo "   Arquivos: $(ls -1 storage/app/public/uploads/avatars 2>/dev/null | wc -l)"
else
    echo "❌ Diretório storage/app/public/uploads/avatars NÃO existe"
    echo "   Criando..."
    mkdir -p storage/app/public/uploads/avatars
    echo "✅ Diretório criado"
fi

echo ""

# 3. Verificar link simbólico
echo "3. Verificando link simbólico public/storage..."

if [ -L "public/storage" ]; then
    target=$(readlink public/storage)
    echo "✅ Link simbólico existe"
    echo "   Aponta para: $target"
    
    if [ -d "public/storage" ]; then
        echo "✅ Link está funcional"
    else
        echo "❌ Link está quebrado"
        echo "   Execute: php artisan storage:link"
    fi
else
    echo "❌ Link simbólico NÃO existe"
    echo "   Execute: php artisan storage:link"
fi

echo ""

# 4. Verificar permissões
echo "4. Verificando permissões..."
echo "   storage/app/public/uploads/avatars: $(stat -c '%a' storage/app/public/uploads/avatars 2>/dev/null || echo 'N/A')"
echo "   public/storage: $(stat -c '%a' public/storage 2>/dev/null || echo 'N/A')"

echo ""

# 5. Verificar últimos uploads
echo "5. Últimos 5 arquivos no diretório de avatars:"
ls -lht storage/app/public/uploads/avatars | head -6

echo ""

# 6. Verificar dados no banco
echo "6. Verificando registros com foto no banco..."
php artisan tinker --execute="
\$usersWithPhoto = \DB::table('users')->whereNotNull('photo')->select('id', 'name', 'photo')->limit(5)->get();
if (\$usersWithPhoto->count() > 0) {
    echo '✅ Encontrados ' . \$usersWithPhoto->count() . ' usuários com foto:\n';
    foreach (\$usersWithPhoto as \$user) {
        echo '   - ID ' . \$user->id . ': ' . \$user->name . '\n';
        echo '     Caminho: ' . \$user->photo . '\n';
        \$fullPath = 'storage/app/public/' . \$user->photo;
        if (file_exists(\$fullPath)) {
            echo '     ✅ Arquivo existe fisicamente\n';
        } else {
            echo '     ❌ Arquivo NÃO existe em ' . \$fullPath . '\n';
        }
    }
} else {
    echo '⚠️  Nenhum usuário com foto cadastrada encontrado\n';
}
"

echo ""
echo "======================================"
echo "DIAGNÓSTICO CONCLUÍDO"
echo "======================================"
