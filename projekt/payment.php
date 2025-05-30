<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

// 🔒 Użytkownik musi być zalogowany
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$memory_id = $_GET['id'] ?? null;

if (!$memory_id || !is_numeric($memory_id)) {
    die("❌ Nieprawidłowe ID wspomnienia.");
}

// 🔍 Pobierz wspomnienie
$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$memory_id, $user_id]);
$memory = $stmt->fetch();

if (!$memory) {
    die("❌ Wspomnienie nie istnieje lub nie należy do Ciebie.");
}

// 🔍 Pobierz dane wysyłkowe z shipping_data
$stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$shipping = $stmt->fetch();

// ✅ Czy dane do wysyłki są kompletne?
$has_shipping = $shipping && 
    !empty($shipping['first_name']) &&
    !empty($shipping['last_name']) &&
    !empty($shipping['street']) &&
    !empty($shipping['postal_code']) &&
    !empty($shipping['city']) &&
    !empty($shipping['email']);

// 📦 Pobierz cenę z produktów
$type = $memory['type'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE type = ?");
$stmt->execute([$type]);
$product = $stmt->fetch();

$price = $product['price'];
$promo = $product['promo_price'];
$final_price = ($promo > 0) ? $promo : $price;

// 🧾 Obsługa formularza płatności
$method = $_POST['method'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$method) {
        $error = "❌ Wybierz metodę płatności.";
    } elseif (!$has_shipping) {
        $error = "❌ Uzupełnij dane do wysyłki przed dokonaniem płatności.";
    } else {
        header("Location: summary.php?id=$memory_id&method=$method");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Płatność za wspomnienie</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .price { font-size: 1.5em; margin-bottom: 10px; }
        .old-price { text-decoration: line-through; color: gray; margin-right: 10px; }
        .new-price { color: darkred; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>💳 Płatność za wspomnienie</h2>

    <p><strong>Numer wspomnienia:</strong> <?= htmlspecialchars($memory['memory_number']) ?></p>
    <p><strong>Rodzaj:</strong> <?= $type === 'family' ? 'Rodzinne' : 'Pojedyncze' ?></p>

    <p class="price">
        <?php if ($promo > 0): ?>
            <span class="old-price"><?= $price ?> zł</span>
            <span class="new-price"><?= $promo ?> zł</span>
        <?php else: ?>
            <strong><?= $price ?> zł</strong>
        <?php endif; ?>
    </p>

    <?php if (!$has_shipping): ?>
        <div class="alert">⚠️ Uzupełnij <a href="shipping.php">dane do wysyłki</a>, aby kontynuować.</div>
    <?php endif; ?>

    <?php if ($error): ?><div class="alert"><?= $error ?></div><?php endif; ?>

    <form method="POST">
        <h4>Wybierz metodę płatności:</h4>

        <label><input type="radio" name="method" value="paypal" <?= $method == 'paypal' ? 'checked' : '' ?>> PayPal</label><br>
        <label><input type="radio" name="method" value="klarna" <?= $method == 'klarna' ? 'checked' : '' ?>> Klarna</label><br>
        <label><input type="radio" name="method" value="przelew" <?= $method == 'przelew' ? 'checked' : '' ?>> Przelew tradycyjny</label><br><br>

        <button type="submit">➡️ Przejdź do podsumowania</button>
    </form>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
