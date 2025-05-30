<?php
session_start();
require_once "inc/config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("❌ Nieprawidłowe ID.");
}

$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$memory = $stmt->fetch();

if (!$memory) {
    die("❌ Wspomnienie nie istnieje lub nie należy do Ciebie.");
}

// Usuń zdjęcia z data
$data = json_decode($memory['data'], true);
foreach ($data as $person) {
    if (!empty($person['main_photo']) && file_exists($person['main_photo'])) {
        unlink($person['main_photo']);
    }
}

// Usuń zdjęcia z galerii
$gallery = json_decode($memory['gallery'], true);
if ($gallery) {
    foreach ($gallery as $img) {
        if (file_exists($img)) unlink($img);
    }
}

// Usuń rekord
$stmt = $pdo->prepare("DELETE FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);

header("Location: my_memories.php");
exit;
