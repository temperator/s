<?php
session_start();
require_once "inc/config.php";

// Domyślne ceny
$single_price = 60;
$single_promo = 0;
$family_price = 80;
$family_promo = 0;

// Pobierz dane z bazy
$stmt = $pdo->query("SELECT * FROM products");
while ($row = $stmt->fetch()) {
    if ($row['name'] == 'single') {
        $single_price = $row['price'];
        $single_promo = $row['old_price']; // promo to old_price
    }
    if ($row['name'] == 'family') {
        $family_price = $row['price'];
        $family_promo = $row['old_price'];
    }
}

$single_price = is_numeric($single_price) ? $single_price : 60;
$family_price = is_numeric($family_price) ? $family_price : 80;


?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Sklep – Digital Cemetery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .old-price {
            text-decoration: line-through;
            color: #888;
            margin-right: 10px;
        }
        .new-price {
            color: #c00;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Pakiety wspomnień</h2>

    <div class="product-box">
        <h3>Wspomnienie pojedyncze</h3>
        <p>
            Cena:
            <?php if ($single_promo > 0): ?>
                <span class="old-price"><?= $single_price ?> €</span>
                <span class="new-price"><?= $single_promo ?> €</span>
            <?php else: ?>
                <strong><?= $single_price ?> €</strong>
            <?php endif; ?>
        </p>
        <p>Umożliwia utworzenie jednego wspomnienia, biografii i galerii zdjęć.</p>

        <?php if (!isset($_SESSION['user'])): ?>
            <a href="login.php" class="btn">Zaloguj się, aby zamówić</a>
        <?php else: ?>
            <a href="create_memory_single.php" class="btn">Utwórz</a>
        <?php endif; ?>
    </div>

    <div class="product-box">
        <h3>Wspomnienie rodzinne</h3>
        <p>
            Cena:
            <?php if ($family_promo > 0): ?>
                <span class="old-price"><?= $family_price ?> €</span>
                <span class="new-price"><?= $family_promo ?> €</span>
            <?php else: ?>
                <strong><?= $family_price ?> €</strong>
            <?php endif; ?>
        </p>
        <p>Umożliwia utworzenie wspomnienia dla dwóch osób z osobnymi biografiami.</p>

        <?php if (!isset($_SESSION['user'])): ?>
            <a href="login.php" class="btn">Zaloguj się, aby zamówić</a>
        <?php else: ?>
            <a href="create_memory_family.php" class="btn">Utwórz</a>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
