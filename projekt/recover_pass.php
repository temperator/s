<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

$error = '';
$success = '';

// 🔄 Dynamiczna captcha
if (!isset($_SESSION['captcha_a']) || !isset($_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = rand(1, 9);
    $_SESSION['captcha_b'] = rand(1, 9);
}
$captcha_question = $_SESSION['captcha_a'] . ' + ' . $_SESSION['captcha_b'] . ' = ?';
$captcha_answer = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $captcha = (int)($_POST['captcha'] ?? 0);
    $honeypot = trim($_POST['honey_url'] ?? '');

    // 🛡️ Walidacje
    if ($honeypot !== '') {
        $error = "Formularz odrzucony (spam wykryty).";
    } elseif (!$email) {
        $error = "Niepoprawny adres email.";
    } elseif ($captcha !== $captcha_answer) {
        $error = "Niepoprawna odpowiedź w captcha.";
    } else {
        // ✅ Symulacja wysyłki resetu (docelowo: generuj token + mail)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Zawsze to samo info, bez zdradzania czy email istnieje
        $success = "Jeśli podany adres istnieje, wysłaliśmy link do resetu hasła.";
    }

    // ♻️ Odśwież captcha przy każdym POST
    $_SESSION['captcha_a'] = rand(1, 9);
    $_SESSION['captcha_b'] = rand(1, 9);
    $captcha_question = $_SESSION['captcha_a'] . ' + ' . $_SESSION['captcha_b'] . ' = ?';
    $captcha_answer = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Odzyskiwanie hasła</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>🔐 Odzyskaj hasło</h2>

    <?php if ($error): ?>
        <div class="alert"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Twój adres email" required>

        <label>Rozwiąż: <?= $captcha_question ?></label>
        <input type="number" name="captcha" required>

        <!-- 🐝 Honeypot -->
        <input type="text" name="honey_url" style="display:none;">

        <button type="submit">📨 Odzyskaj hasło</button>
    </form>
</main>

<?php include 'footer.php'; ?>

</body>
</html>

