<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin(); // tylko admin może to robić

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("❌ Brak ID wspomnienia.");
}

// Sprawdź, czy istnieje
$stmt = $pdo->prepare("SELECT blocked FROM memories WHERE id = ?");
$stmt->execute([$id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    die("❌ Nie znaleziono wspomnienia.");
}

// Przełącz status
$new_status = $memory['blocked'] ? 0 : 1;
$pdo->prepare("UPDATE memories SET blocked = ? WHERE id = ?")->execute([$new_status, $id]);

// Opcjonalnie: komunikat
$status_text = $new_status ? "zablokowane" : "odblokowane";
header("Location: memories.php?id=$id&block=$status_text");
exit;
