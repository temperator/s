<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$msg = '';
$err = '';

// Pobierz dane
$stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$shipping = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => clean($_POST['first_name'] ?? ''),
        'last_name' => clean($_POST['last_name'] ?? ''),
        'company' => clean($_POST['company'] ?? ''),
        'nip' => clean($_POST['nip'] ?? ''),
        'street' => clean($_POST['street'] ?? ''),
        'postal_code' => clean($_POST['postal_code'] ?? ''),
        'city' => clean($_POST['city'] ?? ''),
        'phone' => clean($_POST['phone'] ?? ''),
        'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
        'comment' => clean($_POST['comment'] ?? '')
    ];

    if (!$data['first_name'] || !$data['last_name'] || !$data['street'] || !$data['postal_code'] || !$data['city'] || !$data['phone'] || !$data['email']) {
        $err = "Wypełnij wszystkie wymagane pola.";
    } else {
        if ($shipping) {
            // Update
            $stmt = $pdo->prepare("UPDATE shipping_data SET first_name=?, last_name=?, company=?, nip=?, street=?, postal_code=?, city=?, phone=?, email=?, comment=? WHERE user_id=?");
            $stmt->execute([
                $data['first_name'], $data['last_name'], $data['company'], $data['nip'],
                $data['street'], $data['postal_code'], $data['city'],
                $data['phone'], $data['email'], $data['comment'],
                $user_id
            ]);
            $msg = "Dane wysyłkowe zostały zaktualizowane.";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO shipping_data (user_id, first_name, last_name, company, nip, street, postal_code, city, phone, email, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $data['first_name'], $data['last_name'], $data['company'], $data['nip'],
                $data['street'], $data['postal_code'], $data['city'],
                $data['phone'], $data['email'], $data['comment']
            ]);
            $msg = "Dane wysyłkowe zostały zapisane.";
        }

        // Ponowne pobranie
        $stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $shipping = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dane do wysyłki</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Dane do wysyłki</h2>

    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>

    <form method="POST">
        <input type="text" name="first_name" placeholder="Imię" value="<?= $shipping['first_name'] ?? '' ?>" required>
        <input type="text" name="last_name" placeholder="Nazwisko" value="<?= $shipping['last_name'] ?? '' ?>" required>
        <input type="text" name="company" placeholder="Firma (opcjonalnie)" value="<?= $shipping['company'] ?? '' ?>">
        <input type="text" name="nip" placeholder="NIP (opcjonalnie)" value="<?= $shipping['nip'] ?? '' ?>">
        <input type="text" name="street" placeholder="Ulica i numer domu" value="<?= $shipping['street'] ?? '' ?>" required>
        <input type="text" name="postal_code" placeholder="Kod pocztowy" value="<?= $shipping['postal_code'] ?? '' ?>" required>
        <input type="text" name="city" placeholder="Miasto" value="<?= $shipping['city'] ?? '' ?>" required>
        <input type="text" name="phone" placeholder="Telefon" value="<?= $shipping['phone'] ?? '' ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= $shipping['email'] ?? '' ?>" required>
        <textarea name="comment" placeholder="Komentarz do paczki"><?= $shipping['comment'] ?? '' ?></textarea>

        <button type="submit">Zapisz dane</button>
    </form>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
