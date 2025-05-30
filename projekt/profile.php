<?php
require_once "inc/config.php";
require_once "inc/functions.php";
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'inc/libs/PHPMailer/PHPMailer.php';
require 'inc/libs/PHPMailer/SMTP.php';
require 'inc/libs/PHPMailer/Exception.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$msg = '';
$err = '';

// Liczba wspomnień
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ?");
$stmt->execute([$user_id]);
$memories_count = $stmt->fetchColumn();

// Licznik logowań
$stmt = $pdo->prepare("SELECT COUNT(*) FROM logins WHERE user_id = ?");
$stmt->execute([$user_id]);
$logins_count = $stmt->fetchColumn();

// Obsługa zmiany hasła
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!$current || !$new || !$confirm) {
        $err = "Wszystkie pola muszą być wypełnione.";
    } elseif ($new !== $confirm) {
        $err = "Nowe hasła się nie zgadzają.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();

        if (!$user_data || !password_verify($current, $user_data['password'])) {
            $err = "Nieprawidłowe obecne hasło.";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hash, $user_id]);
            $msg = "✅ Hasło zostało zmienione.";
        }
    }
}

// Obsługa usunięcia konta
if (isset($_POST['delete_account'])) {
    $stmt = $pdo->prepare("SELECT data, gallery FROM memories WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $all_memories = $stmt->fetchAll();

    foreach ($all_memories as $mem) {
        $data = json_decode($mem['data'], true);
        $gallery = json_decode($mem['gallery'], true);

        foreach ($data as $person) {
            if (!empty($person['main_photo']) && file_exists($person['main_photo'])) {
                unlink($person['main_photo']);
            }
        }

        if ($gallery && is_array($gallery)) {
            foreach ($gallery as $img) {
                if (file_exists($img)) unlink($img);
            }
        }
    }

    // Usuwanie danych z bazy
    $pdo->prepare("DELETE FROM memories WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM shipping_data WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM logins WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

    // Wysyłka e-maila pożegnalnego
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'wn31.webd.pl';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@digitaler-friedhof.de';
        $mail->Password   = 'rakowa10'; // ZMIENIĆ NATYCHMIAST NA BEZPIECZNY
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('no-reply@digitaler-friedhof.de', 'Digitaler Friedhof');
        $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);

        $mail->isHTML(true);
        $mail->Subject = '🕊️ Konto zostało usunięte';
        $mail->Body    = "
            <h2>Drogi {$user['first_name']} {$user['last_name']},</h2>
            <p>Twoje konto w serwisie <strong>Digitaler Friedhof</strong> zostało pomyślnie usunięte.</p>
            <p>Dziękujemy za korzystanie z naszych usług. Jeśli chcesz do nas wrócić – zawsze jesteś mile widziany.</p>
            <br>
            <p>Z poważaniem,<br>Zespół Digitaler Friedhof</p>
			
			 <hr>
                    <small>To jest automatyczna wiadomość. Prosimy nie odpowiadać.</small>
			
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Błąd e-maila pożegnalnego: " . $mail->ErrorInfo);
    }

    session_destroy();
    header("Location: goodbye.php");
    exit;
}
?>

 

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Profil użytkownika</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-section { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 20px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 10px; }
        .alert { background: #ffdede; padding: 10px; border: 1px solid #cc0000; color: #a00000; }
        .success { background: #ddffdd; padding: 10px; border: 1px solid #00aa00; color: #008800; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>👤 Profil użytkownika</h2>

    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>

    <div class="form-section">
        <h3>📋 Twoje dane</h3>
        <p><strong>Imię i nazwisko:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Numer klienta:</strong> <?= ($user['id']) ?>/<?= htmlspecialchars($user['client_number']) ?></p>
        
        <p><strong>Liczba wspomnień:</strong> <?= $memories_count ?></p>
       
		
		<p><strong>Liczba logowań:</strong> <?= $logins_count ?? 0 ?></p>
<p><strong>Data rejestracji:</strong>
    <?= isset($user['register_date']) && $user['register_date'] !== '0000-00-00'
        ? formatDate($user['register_date'])
        : 'Brak danych' ?>
</p>

		
		
    </div>

    <div class="form-section">
        <h3>🔐 Zmień hasło</h3>
        <form method="POST">
            <input type="password" name="current_password" placeholder="Obecne hasło" required>
            <input type="password" name="new_password" placeholder="Nowe hasło" required>
            <input type="password" name="confirm_password" placeholder="Powtórz nowe hasło" required>
            <button type="submit" name="change_password">Zmień hasło</button>
        </form>
    </div>

    <div class="form-section">
        <h3>🗑️ Usuń konto</h3>
        <form method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć konto? Tego nie można cofnąć!');">
            <button type="submit" name="delete_account" style="color:red;">❌ Usuń całkowicie konto</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
