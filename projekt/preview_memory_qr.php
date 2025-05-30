<?php
require_once "inc/config.php";
require_once "inc/functions.php";


$hash = $_GET['hash'] ?? '';
if (!$hash) {
    http_response_code(400);
    exit("❌ Brak identyfikatora.");
}

$stmt = $pdo->prepare("SELECT memory_id FROM qr_links WHERE secure_hash = ?");
$stmt->execute([$hash]);
$memory_id = $stmt->fetchColumn();

if (!$memory_id) {
    http_response_code(404);
    exit("❌ Nie znaleziono wspomnienia.");
}

$stmt = $pdo->prepare("
    SELECT m.*, u.first_name AS author_first, u.last_name AS author_last
    FROM memories m
    JOIN users u ON u.id = m.user_id
    WHERE m.id = ?
");
$stmt->execute([$memory_id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    http_response_code(404);
    exit("❌ Wspomnienie nie istnieje.");
}

$pdo->prepare("UPDATE qr_links SET view_count = view_count + 1 WHERE memory_id = ?")->execute([$memory_id]);

// Dekoduj JSON
$data = json_decode($memory['data'], true);
$gallery = json_decode($memory['gallery'], true);

// Jeśli to obiekt – konwertuj na prostą tablicę
if (!empty($gallery) && !array_is_list($gallery)) {
    $gallery = array_values($gallery);
}

$data = json_decode($memory['data'], true);
$person = $data[0] ?? null; // Pierwsza osoba

$main_photo = isset($person['main_photo']) ? $person['main_photo'] : null;




 
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>🕯️ Wspomnienie <?= htmlspecialchars($memory['memory_number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; padding: 30px; max-width: 900px; margin: auto; }
        h1 { font-size: 26px; }
        .person { display: flex; align-items: flex-start; margin-bottom: 40px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .person img { width: 140px; height: auto; border-radius: 8px; margin-right: 20px; object-fit: cover; }
        .person .details { flex: 1; }
        .gallery { margin-top: 30px; }
        .gallery img { max-width: 180px; margin: 8px; border-radius: 6px; box-shadow: 0 0 4px rgba(0,0,0,0.1); }
		
		
    </style>
	
	
	 
</head>
<body>

 




<h1>🕯️ Wspomnienie <?= htmlspecialchars($memory['memory_number']) ?></h1>
<p><strong>Dodane przez:</strong> <?= htmlspecialchars($memory['author_first'] . ' ' . $memory['author_last']) ?></p>
<p><strong>Status:</strong> <?= ucfirst($memory['status']) ?></p>
<hr>





<?php
$data = json_decode($memory['data'], true);
?>

<?php if (is_array($data)): ?>
    <h2>🧍‍♂️🧍‍♀️ Osoby wspomniane</h2>
    <?php foreach ($data as $i => $person): ?>
        <div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; border-radius: 8px;">
            <h3>🧑 Osoba <?= $i + 1 ?></h3>
            <p><strong>Imię i nazwisko:</strong> <?= htmlspecialchars(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? '')) ?></p>
			
			  
            <p><strong>Data urodzenia:</strong> <?= htmlspecialchars($person['birth_date']) ?></p>
            <p><strong>Data śmierci:</strong> <?= htmlspecialchars($person['death_date']) ?></p>
           <!-- <p><strong>Opis:</strong><br>< ?= nl2br(htmlspecialchars($person['bio'])) ?></p>-->
			
			
            <p><strong>Biografia:</strong><br><?= nl2br(htmlspecialchars($person['bio'] ?? '—')) ?></p>

            <?php if (!empty($person['main_photo']) && file_exists(__DIR__ . '/uploads/' . basename($person['main_photo']))): ?>
                <p><strong>📸 Zdjęcie główne:</strong></p>
                <img src="uploads/<?= htmlspecialchars(basename($person['main_photo'])) ?>" alt="Zdjęcie" style="max-width: 100%; border-radius: 8px;">
            <?php else: ?>
                <p class="text-muted">Brak zdjęcia głównego.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-warning">⚠️ Nieprawidłowy format danych wspomnienia.</div>
<?php endif; ?>




 

<?php if (!empty($gallery) && is_array($gallery)): ?>
    <h3>🖼️ Galeria zdjęć</h3>
    <div class="gallery">
        <?php foreach ($gallery as $image): ?>
            <?php if (!empty($image) && file_exists(__DIR__ . "/uploads/" . basename($image))): ?>
                <img src="uploads/<?= htmlspecialchars(basename($image)) ?>" alt="Galeria">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


</body>
</html>
