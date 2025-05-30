<?php
require_once "inc/config.php";

 


// Pobierz hash
$hash = $_GET['hash'] ?? null;
if (!$hash) die("Brak hashu");

// Pobierz dane po hash
$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = (SELECT memory_id FROM qr_links WHERE secure_hash = ?)");
$stmt->execute([$hash]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) die("Nie znaleziono wspomnienia");

//liczenie odwiedzin
if (isset($_GET['code'])) {
    $stmt = $pdo->prepare("UPDATE qr_links SET view_count = view_count + 1 WHERE secure_hash = ?");
    $stmt->execute([$_GET['code']]);
}


// Znajdź wspomnienie powiązane z tym hashem
$stmt = $pdo->prepare("SELECT m.*, u.first_name, u.last_name 
    FROM qr_links q 
    JOIN memories m ON m.id = q.memory_id 
    JOIN users u ON u.id = m.user_id 
    WHERE q.secure_hash = ?");
$stmt->execute([$hash]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    http_response_code(404);
    exit("❌ Nie znaleziono wspomnienia.");
}

// Dane JSON
$data = json_decode($memory['data'], true);
$person = $data[0] ?? null;

if (!$person) {
    exit("❌ Błąd danych wspomnienia.");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wspomnienie: <?= htmlspecialchars($person['first_name']) ?> <?= htmlspecialchars($person['last_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .memory-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: auto;
        }
        .memory-box img {
            max-width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="memory-box text-center">
    <h2><?= htmlspecialchars($person['first_name']) ?> <?= htmlspecialchars($person['last_name']) ?></h2>
    <?php if (!empty($person['main_photo'])): ?>
        <img src="<?= htmlspecialchars($person['main_photo']) ?>" alt="Zdjęcie">
    <?php endif; ?>
    <p><strong>Data urodzenia:</strong> <?= htmlspecialchars($person['birth_date']) ?></p>
    <p><strong>Data śmierci:</strong> <?= htmlspecialchars($person['death_date']) ?></p>
    <p><strong>Opis:</strong> <?= nl2br(htmlspecialchars($person['bio'])) ?></p>
</div>
</body>
</html>
