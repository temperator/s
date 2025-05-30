<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = $_POST['website'] ?? '';
    $name     = strip_tags(trim($_POST['name'] ?? ''));
    $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message  = strip_tags(trim($_POST['message'] ?? ''));
    $memory   = strip_tags(trim($_POST['memory_number'] ?? ''));
    $captcha  = trim($_POST['captcha'] ?? '');
    $captcha_correct = $_SESSION['captcha_answer'] ?? '';

    if ($honeypot !== '') {
        $err = "❌ SPAM wykryty.";
    } elseif (!$name || !$email || !$message || !$memory) {
        $err = "❌ Wszystkie pola są wymagane.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "❌ Nieprawidłowy email.";
    } elseif ($captcha !== $captcha_correct) {
        $err = "❌ Błędna odpowiedź CAPTCHA.";
    } else {
        $to = 'temperator@interia.pl';  // docelowy e-mail
        $subject = "📩 Wiadomość od $name (wspomnienie: $memory)";
        $headers  = "From: Kontakt <kontakt@twojadomena.pl>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $body = "
        <h3>📨 Nowa wiadomość:</h3>
        <p><b>Imię i nazwisko:</b> $name</p>
        <p><b>Email:</b> $email</p>
        <p><b>Numer wspomnienia:</b> $memory</p>
        <p><b>Treść:</b><br>$message</p>
        ";

        if (mail($to, $subject, $body, $headers)) {
            $msg = "✅ Wiadomość wysłana!";
        } else {
            $err = "❌ Błąd wysyłki.";
        }
    }

    // Nowa CAPTCHA po każdej próbie
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha_answer'] = (string)($a + $b);
    $captcha_question = "$a + $b = ?";
} else {
    // Pierwsze ładowanie
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha_answer'] = (string)($a + $b);
    $captcha_question = "$a + $b = ?";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>📩 Kontakt</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <h2>📩 Formularz kontaktowy</h2>
    <?php if (!empty($err)): ?><div class="alert"><?= $err ?></div><?php endif; ?>
    <?php if (!empty($msg)): ?><div class="success"><?= $msg ?></div><?php endif; ?>

    <form method="post">
        <!-- honeypot -->
        <input type="text" name="website" style="display:none">

        <label>Imię i nazwisko:</label>
        <input type="text" name="name" required>

        <label>Adres e-mail:</label>
        <input type="email" name="email" required>

        <label>Numer wspomnienia (np. 048/12):</label>
        <input type="text" name="memory_number" required>

        <label>Wiadomość:</label>
        <textarea name="message" rows="6" required></textarea>

        <label>Potwierdź: <?= $captcha_question ?></label>
        <input type="text" name="captcha" required>

        <label>Potwierdź, że jesteś człowiekiem:</label>
        <input type="radio" onclick="document.getElementById('submitBtn').disabled=true"> Robot
        <input type="radio" onclick="document.getElementById('submitBtn').disabled=false"> Człowiek

        <br><br>
        <button type="submit" id="submitBtn" disabled>📨 Wyślij</button>
    </form>
</main>

<?php include 'footer.php'; ?>
</body>
</html>