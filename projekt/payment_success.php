<?php
session_start();
require_once "inc/config.php";

// Sprawdź usera
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$memory_id = $_GET['memory_id'] ?? null;
$method = $_GET['method'] ?? '';

if (!$memory_id || !$method) {
    die("Błąd danych");
}

// 🔓 Aktywuj wspomnienie
$stmt = $pdo->prepare("UPDATE memories SET is_paid = 1, active_until = DATE_ADD(NOW(), INTERVAL 5 YEAR) WHERE id = ? AND user_id = ?");
$stmt->execute([$memory_id, $_SESSION['user']['id']]);

// ✅ Zmień status płatności
$pdo->prepare("UPDATE payments SET status = 'success', paid_at = NOW() WHERE memory_id = ?")->execute([$memory_id]);

// ➡️ Dalej do wspomnień
header("Location: my_memories.php?msg=wspomnienie+aktywne");
exit;
