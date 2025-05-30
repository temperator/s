<?php
require_once "inc/config.php";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_repeat = $_POST['password_repeat'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Nieprawidłowy adres email.";
    } elseif ($password !== $password_repeat) {
        $error = "Hasła nie są zgodne.";
    } elseif (strlen($password) < 1) {
        $error = "Hasło musi mieć co najmniej 1 znaków.";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed]);
            $success = "Administrator został dodany.";
        } catch (PDOException $e) {
            $error = "Błąd: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Tworzenie administratora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include "header.php"; ?>
<body>
<div class="container">
    <h2 class="text-center">?? Dodaj Administratora</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="form-horizontal" style="max-width: 500px; margin: auto;">
        <div class="form-group">
            <label>Email admina</label>
            <input type="email" name="email" class="form-control" required placeholder="admin@twojadomena.pl">
        </div>
        <div class="form-group">
            <label>Hasło</label>
            <input type="password" name="password" class="form-control" required placeholder="Min. 6 znaków">
        </div>
        <div class="form-group">
            <label>Powtórz hasło</label>
            <input type="password" name="password_repeat" class="form-control" required>
        </div>
        <button class="btn btn-success">Utwórz administratora</button>
    </form>
</div>
<?php include "footer.php"; ?>
</body>
</html>
