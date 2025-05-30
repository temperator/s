<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

// 🔒 Autoryzacja
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$memory_id = $_GET['id'] ?? null;
$method = $_GET['method'] ?? null;

if (!$memory_id || !is_numeric($memory_id) || !$method) {
    die("❌ Nieprawidłowe dane do podsumowania.");
}

// 🔍 Pobierz wspomnienie
$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$memory_id, $user_id]);
$memory = $stmt->fetch();
if (!$memory) die("❌ Wspomnienie nie istnieje lub nie należy do Ciebie.");

// 🔍 Pobierz dane wysyłkowe
$stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$shipping = $stmt->fetch();
if (!$shipping) die("❌ Brak danych do wysyłki.");

// 🔍 Pobierz produkt
$stmt = $pdo->prepare("SELECT * FROM products WHERE type = ?");
$stmt->execute([$memory['type']]);
$product = $stmt->fetch();

$price = $product['price'];
$promo = $product['promo_price'];
$final_price = ($promo > 0) ? $promo : $price;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podsumowanie zamówienia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <h2>📦 Podsumowanie zamówienia</h2>

    <p><strong>Numer wspomnienia:</strong> <?= htmlspecialchars($memory['memory_number']) ?></p>
    <p><strong>Rodzaj:</strong> <?= $memory['type'] === 'family' ? 'Rodzinne' : 'Pojedyncze' ?></p>
    <p><strong>Metoda płatności:</strong> <?= strtoupper($method) ?></p>

    <h3>💰 Kwota do zapłaty:</h3>
    <p style="font-size: 1.5em; font-weight:bold;">
        <?= $final_price ?> zł
    </p>

    <h3>📬 Dane do wysyłki:</h3>
    <p><?= htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name']) ?></p>
    <?php if ($shipping['company']): ?><p><?= htmlspecialchars($shipping['company']) ?></p><?php endif; ?>
    <?php if ($shipping['nip']): ?><p>NIP: <?= htmlspecialchars($shipping['nip']) ?></p><?php endif; ?>
    <p><?= htmlspecialchars($shipping['street']) ?></p>
    <p><?= htmlspecialchars($shipping['postal_code'] . ' ' . $shipping['city']) ?></p>
    <p>📞 <?= htmlspecialchars($shipping['phone']) ?></p>
    <p>✉️ <?= htmlspecialchars($shipping['email']) ?></p>
    <?php if ($shipping['comment']): ?><p><em><?= nl2br(htmlspecialchars($shipping['comment'])) ?></em></p><?php endif; ?>

    <hr>

    <form method="POST" action="process_payment.php">
        <input type="hidden" name="memory_id" value="<?= $memory_id ?>">
        <input type="hidden" name="method" value="<?= $method ?>">
        <input type="hidden" name="amount" value="<?= $final_price ?>">

        <button type="submit">💳 Zapłać teraz</button>
    </form>

    <p style="margin-top:20px;"><a href="payment.php?id=<?= $memory_id ?>">← Wróć do wyboru metody płatności</a></p>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
