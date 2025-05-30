<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security_user.php";
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}


$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $err = "Wprowadź e-mail i hasło.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'client_number' => $user['client_number'],
                'register_date' => $user['register_date']
            ];

            $pdo->prepare("INSERT INTO logins (user_id, login_date, user_agent, ip_address) VALUES (?, NOW(), ?, ?)")
                ->execute([$user['id'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']]);

            header("Location: dashboard.php");
            exit;
        } else {
            $err = "Nieprawidłowy e-mail lub hasło.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <h2>🔐 Logowanie</h2>
    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zaloguj się</button>
    </form>
    <p><a href="recover_pass.php">🔁 Odzyskaj hasło</a></p>
    <p><a href="register.php">➕ Zarejestruj się</a></p>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
