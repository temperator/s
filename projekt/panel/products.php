<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

// Walidacja i zapis danych
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prices'])) {
    $fields = ['single_price', 'single_old', 'family_price', 'family_old'];
    foreach ($fields as $f) {
        if (!isset($_POST[$f]) || !is_numeric($_POST[$f])) {
            die("❌ Nieprawidłowa wartość pola: $f");
        }
    }

    $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE name = 'single'")
        ->execute([$_POST['single_price'], $_POST['single_old']]);

    $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE name = 'family'")
        ->execute([$_POST['family_price'], $_POST['family_old']]);

    header("Location: products.php?success=1");
    exit;
}

// Pobierz dane
$stmt = $pdo->query("SELECT name, price, old_price FROM products");
$all = $stmt->fetchAll(PDO::FETCH_UNIQUE);

if (!isset($all['single'], $all['family'])) {
    die("❌ Brak wymaganych rekordów 'single' i 'family' w tabeli products.");
}

$single = $all['single'];
$family = $all['family'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>💰 Cennik produktów</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "header.php"; ?>

<div class="container my-5">
    <h3 class="mb-3">💰 Cennik produktów</h3>
    <p class="text-muted">Zarządzaj cenami wspomnień oraz promocjami (stara cena).</p>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">✅ Ceny zostały zaktualizowane.</div>
    <?php endif; ?>

    <form method="post" class="row g-4 mt-3">

        <div class="col-md-6">
            <label class="form-label fw-bold">🧍 Cena (Pojedyncze)</label>
            <div class="input-group">
                <input type="number" name="single_price" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($single['price']) ?>">
                <span class="input-group-text">EUR</span>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">🕒 Stara cena (Pojedyncze)</label>
            <div class="input-group">
                <input type="number" name="single_old" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($single['old_price']) ?>">
                <span class="input-group-text">EUR</span>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">👨‍👩‍👧‍👦 Cena (Rodzinne)</label>
            <div class="input-group">
                <input type="number" name="family_price" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($family['price']) ?>">
                <span class="input-group-text">EUR</span>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">🕒 Stara cena (Rodzinne)</label>
            <div class="input-group">
                <input type="number" name="family_old" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($family['old_price']) ?>">
                <span class="input-group-text">EUR</span>
            </div>
        </div>

        <div class="col-12 text-center mt-4">
            <button type="submit" name="save_prices" class="btn btn-success px-5 py-2">💾 Zapisz ceny</button>
        </div>
    </form>
</div>

<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
