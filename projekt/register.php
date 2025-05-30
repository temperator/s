<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security_user.php";
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}



require 'inc/libs/PHPMailer/PHPMailer.php';
require 'inc/libs/PHPMailer/SMTP.php';
require 'inc/libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

function generateCaptcha()
{
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha_q'] = "$a + $b";
    $_SESSION['captcha_token'] = hash('sha256', $a + $b);
}

if (!isset($_SESSION['captcha_token'])) {
    generateCaptcha();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = clean($_POST['first_name'] ?? '');
    $last_name  = clean($_POST['last_name'] ?? '');
    $email      = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $pass       = $_POST['password'] ?? '';
    $pass2      = $_POST['password2'] ?? '';
    $captcha    = trim($_POST['captcha'] ?? '');
    $honeypot   = trim($_POST['url_fake'] ?? '');
    $regulamin  = isset($_POST['accept_rules']);

    if ($honeypot !== '') {
        $error = "Wykryto próbę spamu.";
    } elseif (!$first_name || !$last_name || !$email || !$pass || !$pass2) {
        $error = "Wszystkie pola są wymagane.";
    } elseif (!$email) {
        $error = "Niepoprawny adres email.";
    } elseif (strlen($pass) < 6) {
        $error = "Hasło musi mieć co najmniej 6 znaków.";
    } elseif ($pass !== $pass2) {
        $error = "Hasła się nie zgadzają.";
    } elseif (!$regulamin) {
        $error = "Musisz zaakceptować przetwarzanie danych.";
    } elseif (hash('sha256', $captcha) !== $_SESSION['captcha_token']) {
        $error = "Niepoprawna odpowiedź na pytanie.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Ten adres email jest już zarejestrowany.";
        } else {
            $lastId = $pdo->query("SELECT MAX(id) FROM users")->fetchColumn();
            $client_number = generateClientNumber($lastId);
            $hashed = password_hash($pass, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, client_number, register_date) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$first_name, $last_name, $email, $hashed, $client_number]);

            // MAIL
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'wn31.webd.pl';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'no-reply@digitaler-friedhof.de';
                $mail->Password   = 'rakowa10';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('no-reply@digitaler-friedhof.de', 'Digitaler Friedhof');
                $mail->addAddress($email, $first_name . ' ' . $last_name);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->Subject = 'Witaj w Digitaler Friedhof!';
                $mail->Body = "
                    <h2>Dziękujemy za rejestrację!</h2>
                    <p>Witaj, <strong>$first_name</strong>!</p>
                    <p>Twoje konto zostało pomyślnie utworzone.</p>
                    <p><a href='https://digitaler-friedhof.de/login.php'>👉 Zaloguj się</a></p>
                    <hr><small>To jest automatyczna wiadomość. Nie odpowiadaj na nią.</small>
                ";
                $mail->send();
            } catch (Exception $e) {
                error_log("📧 Błąd email: " . $mail->ErrorInfo);
            }

            // LOGOWANIE REJESTRACJI
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $pdo->prepare("INSERT INTO registration_attempts (email, ip_address, user_agent, success, is_bot) VALUES (?, ?, ?, ?, ?)")
                ->execute([$email, $ip, $agent, 1, 0]);

            unset($_SESSION['captcha_token'], $_SESSION['captcha_q']);
            header("Location: loading.php?redirect=my_memories.php&msg=Konto+utworzone...&delay=2");
            exit;
        }
    }

    // ❌ Nieudana rejestracja
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $pdo->prepare("INSERT INTO registration_attempts (email, ip_address, user_agent, success, is_bot) VALUES (?, ?, ?, ?, ?)")
        ->execute([$email, $ip, $agent, 0, $honeypot !== '' ? 1 : 0]);

    generateCaptcha(); // Odśwież
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <h2>🔐 Rejestracja użytkownika</h2>
    <?php if ($error): ?><div class="alert"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="text" name="first_name" placeholder="Imię" required>
        <input type="text" name="last_name" placeholder="Nazwisko" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <input type="password" name="password2" placeholder="Powtórz hasło" required>
        <label>Rozwiąż: <?= $_SESSION['captcha_q'] ?></label>
        <input type="number" name="captcha" required>
        <input type="checkbox" name="accept_rules" required> Zgadzam się na przetwarzanie danych.
        <input type="text" name="url_fake" style="display:none">
        <button type="submit">Zarejestruj się</button>
    </form>
    <p><a href="login.php">Masz konto? Zaloguj się</a></p>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
