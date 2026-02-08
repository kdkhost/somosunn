<?php
// Helper to get .env values
function getEnvValue($key, $default = null)
{
    if (!file_exists('.env'))
        return $default;
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        $parts = explode('=', $line, 2);
        if (count($parts) < 2)
            continue;
        if (trim($parts[0]) === $key)
            return trim($parts[1], '"\' ');
    }
    return $default;
}

$db_host = getEnvValue('DB_HOST', '127.0.0.1');
$db_name = getEnvValue('DB_DATABASE');
$db_user = getEnvValue('DB_USERNAME');
$db_pass = getEnvValue('DB_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get mentor ID
    $stmt = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'superadmin') ORDER BY id ASC LIMIT 1");
    $mentorId = $stmt->fetchColumn() ?: 1;

    $title = 'Conexão Elite: Mentoria de Negócios 2026';
    $desc = 'Uma mentoria exclusiva focada em estratégias de escala, networking de alto nível e automação de processos para empresários que desejam dobrar seu faturamento.';
    $schedule = json_encode([
        'Segunda-feira' => '19:00 - 21:00',
        'Quarta-feira' => '19:00 - 21:00'
    ]);

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM mentorships WHERE title = ?");
    $stmt->execute([$title]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $stmt = $pdo->prepare("UPDATE mentorships SET mentor_id = ?, description = ?, price = ?, slots = ?, schedule = ?, type = ?, video_platform = ?, video_link = ?, demo_link = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$mentorId, $desc, 1497.00, 10, $schedule, 'online', 'google_meet', 'https://meet.google.com/abc-defn-ghi', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', $existingId]);
        echo "Mentoria ATUALIZADA com sucesso ID: $existingId\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO mentorships (title, mentor_id, description, price, slots, schedule, type, video_platform, video_link, demo_link, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$title, $mentorId, $desc, 1497.00, 10, $schedule, 'online', 'google_meet', 'https://meet.google.com/abc-defn-ghi', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        echo "Mentoria CRIADA com sucesso via PDO!\n";
    }

} catch (\PDOException $e) {
    echo "ERRO DE CONEXÃO: " . $e->getMessage() . "\n";
    exit(1);
}
