<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

// 1. Sprawdź ID
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: memories.php?error=missing_id");
    exit;
}

// 2. Sprawdź czy wspomnienie istnieje i jest nieopłacone
$stmt = $pdo->prepare("SELECT is_paid, status FROM memories WHERE id = ?");
$stmt->execute([$id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    header("Location: memories.php?error=not_found");
    exit;
}

if ($memory['is_paid']) {
    header("Location: memories.php?error=already_paid");
    exit;
}

// 3. Ustaw is_paid = 1 i opcjonalnie domyślny status jeśli pusty
$newStatus = $memory['status'] ?: 'oczekujący';

$update = $pdo->prepare("UPDATE memories SET is_paid = 1, status = ? WHERE id = ?");
$update->execute([$newStatus, $id]);

// 4. Przekierowanie z komunikatem
header("Location: memories.php?success=activated");
exit;
