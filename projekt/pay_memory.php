<?php
session_start();
require_once "inc/config.php";
require_once "inc/security_user.php";
requireLogin();

$user_id = $_SESSION['user']['id'];
$memory_id = intval($_GET['id'] ?? 0);

// Pobierz wspomnienie i kwotę
$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$memory_id, $user_id]);
$memory = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memory) {
    http_response_code(404);
    exit("❌ Nie znaleziono wspomnienia.");
}

$amount = number_format($memory['price'], 2, '.', '');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Płatność za wspomnienie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        .pay-card {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .method-box {
            border: 2px solid transparent;
            padding: 1rem;
            border-radius: 8px;
            transition: 0.3s ease;
        }
        .method-box:hover {
            border-color: #0d6efd;
            background-color: #f0f8ff;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container my-5">
    <div class="pay-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-4">💳 Wybierz metodę płatności</h3>
        <p><strong>Wspomnienie:</strong> <?= htmlspecialchars($memory['memory_number']) ?></p>
        <p><strong>Do zapłaty:</strong> <span class="text-success"><?= $amount ?> €</span></p>

        <div class="row mt-4">
            <!-- PayPal -->
            <div class="col-md-12 mb-3">
                <form action="paypal_redirect.php" method="GET">
                    <input type="hidden" name="id" value="<?= $memory['id'] ?>">
                    <button type="submit" class="btn btn-outline-primary w-100 method-box">
                        🅿️ Zapłać przez PayPal
                    </button>
                </form>
            </div>

            <!-- Klarna -->
            <div class="col-md-12 mb-3">
                <form action="klarna_redirect.php" method="GET">
                    <input type="hidden" name="id" value="<?= $memory['id'] ?>">
                    <button type="submit" class="btn btn-outline-dark w-100 method-box">
                        🧾 Zapłać przez Klarna
                    </button>
                </form>
            </div>

            <!-- Przelew -->
            <div class="col-md-12 mb-3">
                <div class="border p-3 bg-light rounded">
                    <h5>🏦 Przelew bankowy</h5>
                    <p><strong>Odbiorca:</strong> <?= htmlspecialchars(SITE_NAME) ?></p>
                    <p><strong>IBAN:</strong> DE12345678901234567890</p>
                    <p><strong>BIC:</strong> DEUTDEDBXXX</p>
                    <p><strong>Tytuł:</strong> <?= htmlspecialchars($memory['memory_number']) ?></p>
                    <p><strong>Kwota:</strong> <?= $amount ?> €</p>
                    <small class="text-muted">Po zaksięgowaniu płatności wspomnienie zostanie aktywowane ręcznie.</small>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="my_memories.php" class="btn btn-secondary">↩️ Wróć</a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
