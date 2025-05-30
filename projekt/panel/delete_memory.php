<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin(); // dostęp tylko dla admina

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("❌ Brak ID wspomnienia.");
}

// Pobierz dane wspomnienia (dla usunięcia plików)
$stmt = $pdo->prepare("SELECT data, gallery FROM memories WHERE id = ?");
$stmt->execute([$id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    die("❌ Nie znaleziono wspomnienia.");
}

// 🖼️ Usuń zdjęcia z JSON (dane)
$dataArray = json_decode($memory['data'], true);
if (is_array($dataArray)) {
    foreach ($dataArray as $person) {
        if (!empty($person['main_photo'])) {
            $file = __DIR__ . "/../" . $person['main_photo'];
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// 🖼️ Usuń galerię (może być array lub obiekt)
$gallery = json_decode($memory['gallery'], true);
if (is_array($gallery)) {
    foreach ($gallery as $item) {
        if (is_array($item)) {
            foreach ($item as $img) {
                $file = __DIR__ . "/../" . $img;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            $file = __DIR__ . "/../" . $item;
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// 🔄 Usuń powiązania
$pdo->prepare("DELETE FROM payments WHERE memory_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM qr_links WHERE memory_id = ?")->execute([$id]);

// 🧼 Usuń wspomnienie
$pdo->prepare("DELETE FROM memories WHERE id = ?")->execute([$id]);

// ✅ Powrót z potwierdzeniem
header("Location: memories.php?deleted=1");
exit;
