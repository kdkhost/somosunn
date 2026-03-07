<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h2>Forcando Adicao da Coluna is_somos_unicas...</h2>";

try {
    // EVENTS
    if (!\Illuminate\Support\Facades\Schema::hasColumn('events', 'is_somos_unicas')) {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE events ADD COLUMN is_somos_unicas TINYINT(1) DEFAULT 0 AFTER title");
        echo "Coluna adicionada em events.<br>";
    } else {
        echo "events ja possui a coluna.<br>";
    }

    // COURSES
    if (!\Illuminate\Support\Facades\Schema::hasColumn('courses', 'is_somos_unicas')) {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE courses ADD COLUMN is_somos_unicas TINYINT(1) DEFAULT 0 AFTER title");
        echo "Coluna adicionada em courses.<br>";
    } else {
        echo "courses ja possui a coluna.<br>";
    }

    // MENTORSHIPS
    if (!\Illuminate\Support\Facades\Schema::hasColumn('mentorships', 'is_somos_unicas')) {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mentorships ADD COLUMN is_somos_unicas TINYINT(1) DEFAULT 0 AFTER title");
        echo "Coluna adicionada em mentorships.<br>";
    } else {
        echo "mentorships ja possui a coluna.<br>";
    }

    // E como a migration oficial tá presa num limbo ("nothing to migrate" mas sem a coluna),
    // a gente força a inserção dela no migrations pra ele parar de tentar rodar depois.
    $exists = \Illuminate\Support\Facades\DB::table('migrations')
        ->where('migration', '2026_03_07_150344_add_is_somos_unicas_to_tables')
        ->exists();

    if (!$exists) {
        $batch = \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1;
        \Illuminate\Support\Facades\DB::table('migrations')->insert([
            'migration' => '2026_03_07_150344_add_is_somos_unicas_to_tables',
            'batch' => $batch
        ]);
        echo "Migration registrada no banco.<br>";
    }

    echo '<h3>Sucesso! O banco de dados foi atualizado forcadamente <a href="/somos-unicas">Clique aqui para ir para Somos Unicas</a></h3>';

} catch (\Exception $e) {
    echo "<h3>Erro:</h3>" . $e->getMessage();
}
