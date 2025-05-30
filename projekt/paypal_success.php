<?php
require_once "inc/config.php";

$data = json_decode(file_get_contents("php://input"), true);
$memory_id = intval($data['memory_id'] ?? 0);

if (!$memory_id) {
    http_response_code(400);
    exit("Błąd danych.");
}

// ustaw płatność w bazie
$stmt = $pdo->prepare("INSERT INTO payments (memory_id, status, method, amount, paid_at)
VALUES (?, 'completed', 'paypal', 19.99, NOW())");
$stmt->execute([$memory_id]);

// aktywuj wspomnienie
$pdo->prepare("UPDATE memories SET is_paid = 1, status = 'aktywny' WHERE id = ?")->execute([$memory_id]);

echo "OK";
