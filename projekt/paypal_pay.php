<?php
session_start();
require_once "inc/config.php";

$memory_id = $_POST['memory_id'] ?? null;
$amount = $_POST['amount'] ?? 0;

// Tu normalnie generowałbyś zamówienie PayPal przez API
// ale my symulujemy sukces

// ⏳ Zapisz do płatności (opcjonalne)
$stmt = $pdo->prepare("INSERT INTO payments (memory_id, user_id, method, amount, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->execute([$memory_id, $_SESSION['user']['id'], 'paypal', $amount]);

// 🔁 Przekieruj do success
header("Location: payment_success.php?memory_id=$memory_id&method=paypal");
exit;
