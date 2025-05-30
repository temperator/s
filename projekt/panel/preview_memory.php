
<?php
require_once "inc/config.php";

$hash = $_GET['hash'] ?? '';
if (!$hash) {
    http_response_code(400);
    exit("? Brak identyfikatora.");
}







$stmt = $pdo->prepare("SELECT memory_id FROM qr_links WHERE secure_hash = ?");
$stmt->execute([$hash]);
$memory_id = $stmt->fetchColumn();

if (!$memory_id) {
    http_response_code(404);
    exit("? Nie znaleziono wspomnienia.");
}

$stmt = $pdo->prepare("
    SELECT m.*, u.first_name, u.last_name
    FROM memories m
    JOIN users u ON u.id = m.user_id
    WHERE m.id = ?
");
$stmt->execute([$memory_id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    http_response_code(404);
    exit("? Wspomnienie nie istnieje.");
}

// Zlicz
$pdo->prepare("UPDATE qr_links SET view_count = view_count + 1 WHERE memory_id = ?")->execute([$memory_id]);

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>??? <?= htmlspecialchars($memory['memory_number']) ?></title>
</head>
<body>
    <h1>Wspomnienie <?= htmlspecialchars($memory['memory_number']) ?></h1>
    <p>Autor: <?= htmlspecialchars($memory['first_name'] . ' ' . $memory['last_name']) ?></p>
    <p>Status: <?= ucfirst($memory['status']) ?></p>
    <p>Opis: <?= nl2br(htmlspecialchars($memory['content'] ?? '—')) ?></p>
</body>
</html>
