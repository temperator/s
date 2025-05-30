<?php
session_start();
require_once "inc/config.php";
require_once "inc/security_user.php";
requireLogin();

$user_id = $_SESSION['user']['id'];
$memory_id = (int)($_POST['id'] ?? 0);

// Pobierz wspomnienie użytkownika
$stmt = $pdo->prepare("SELECT data, gallery FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$memory_id, $user_id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    header("Location: my_memories.php?error=not_found");
    exit;
}

// Zbierz zdjęcia
$files = [];

$data = json_decode($memory['data'], true);
foreach ($data as $person) {
    if (!empty($person['main_photo'])) {
        $files[] = $person['main_photo'];
    }
}

$gallery = json_decode($memory['gallery'], true);
if (is_array($gallery)) {
    foreach ($gallery as $img) {
        $files[] = $img;
    }
}

// Usuń pliki z dysku
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        unlink($path);
    }
}

// Usuń wpis z bazy
$pdo->prepare("DELETE FROM memories WHERE id = ? AND user_id = ?")->execute([$memory_id, $user_id]);

header("Location: my_memories.php?deleted=1");
exit;
