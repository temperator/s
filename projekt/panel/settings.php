<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

// Pobierz aktualne ustawienia
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Zapis formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_title', 'site_description', 'site_keywords', 'slogan', 'subscription_days',
        'domain', 'admin_email', 'paypal_client_id', 'paypal_secret',
        'klarna_id', 'klarna_secret', 'bank_name', 'bank_account', 'bank_iban', 'bank_swift'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? '';
    }

    // Sprawdź czy istnieją ustawienia
    if ($settings) {
        $sql = "UPDATE settings SET " . implode(", ", array_map(fn($f) => "$f = :$f", $fields)) . " WHERE id = :id";
        $data['id'] = $settings['id'];
    } else {
        $sql = "INSERT INTO settings (" . implode(", ", $fields) . ") VALUES (" . implode(", ", array_map(fn($f) => ":$f", $fields)) . ")";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    header("Location: settings.php?ok");
    exit;
}
?>

<?php include "header.php"; ?>

<div class="container mt-5">
    <h3 class="mb-4">⚙️ Ustawienia systemowe</h3>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">Zapisano ustawienia.</div>
    <?php endif; ?>

    <form method="POST" class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Tytuł strony</label>
            <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Slogan</label>
            <input type="text" name="slogan" class="form-control" value="<?= htmlspecialchars($settings['slogan'] ?? '') ?>">
        </div>

        <div class="col-md-12">
            <label class="form-label">Opis strony</label>
            <textarea name="site_description" class="form-control" rows="2"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-12">
            <label class="form-label">Słowa kluczowe (SEO)</label>
            <textarea name="site_keywords" class="form-control" rows="2"><?= htmlspecialchars($settings['site_keywords'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email administratora</label>
            <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Domena systemu</label>
            <input type="text" name="domain" class="form-control" value="<?= htmlspecialchars($settings['domain'] ?? '') ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Czas aktywacji (dni)</label>
            <input type="number" name="subscription_days" class="form-control" value="<?= htmlspecialchars($settings['subscription_days'] ?? 1835) ?>">
        </div>

        <!-- PAYPAL -->
        <div class="col-12"><hr><h5>💳 PayPal</h5></div>

        <div class="col-md-6">
            <label class="form-label">PayPal Client ID</label>
            <input type="text" name="paypal_client_id" class="form-control" value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">PayPal Secret</label>
            <input type="text" name="paypal_secret" class="form-control" value="<?= htmlspecialchars($settings['paypal_secret'] ?? '') ?>">
        </div>

        <!-- KLARNA -->
        <div class="col-12"><hr><h5>💳 Klarna</h5></div>

        <div class="col-md-6">
            <label class="form-label">Klarna ID</label>
            <input type="text" name="klarna_id" class="form-control" value="<?= htmlspecialchars($settings['klarna_id'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Klarna Secret</label>
            <input type="text" name="klarna_secret" class="form-control" value="<?= htmlspecialchars($settings['klarna_secret'] ?? '') ?>">
        </div>

        <!-- PRZELEW BANKOWY -->
        <div class="col-12"><hr><h5>🏦 Dane do przelewu</h5></div>

        <div class="col-md-6">
            <label class="form-label">Nazwa banku</label>
            <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Numer konta</label>
            <input type="text" name="bank_account" class="form-control" value="<?= htmlspecialchars($settings['bank_account'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">IBAN</label>
            <input type="text" name="bank_iban" class="form-control" value="<?= htmlspecialchars($settings['bank_iban'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">SWIFT</label>
            <input type="text" name="bank_swift" class="form-control" value="<?= htmlspecialchars($settings['bank_swift'] ?? '') ?>">
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">💾 Zapisz ustawienia</button>
        </div>
    </form>
</div>

<?php include "footer.php"; ?>
