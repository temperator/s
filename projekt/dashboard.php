<?php
require_once "inc/config.php";
require_once "inc/functions.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Sprawdź dane kontaktowe
$stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$shipping = $stmt->fetch();

$missing_shipping_data = !$shipping || empty($shipping['first_name']) || empty($shipping['last_name']) || empty($shipping['street']) || empty($shipping['postal_code']) || empty($shipping['city']) || empty($shipping['email']);


// Liczba wspomnień
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ?");
$stmt->execute([$user_id]);
$memories_count = $stmt->fetchColumn();

// Liczba opłaconych wspomnień
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ? AND is_paid = 1");
$stmt->execute([$user_id]);
$paid_memories = $stmt->fetchColumn();

// Wspomnienia wygasające w ciągu 30 dni
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ? AND active_until <= CURDATE() + INTERVAL 30 DAY AND active_until IS NOT NULL");
$stmt->execute([$user_id]);
$expiring_memories = $stmt->fetchColumn();

// Ostatnie logowanie
$stmt = $pdo->prepare("SELECT MAX(login_date) FROM logins WHERE user_id = ?");
$stmt->execute([$user_id]);
$last_login = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 10px 0;
            border-radius: 6px;
            background-color: #f9f9f9;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .btn {
            display: inline-block;
            background: #007BFF;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <h2>Witaj, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>!</h2>
    <p><strong>Twój numer klienta:</strong> <?=  ($user['id']) ?>/<?= htmlspecialchars($user['client_number']) ?></p>

    <div class="dashboard-grid">
        <div class="dashboard-box">
            <h4>📁 Twoje wspomnienia</h4>
            <p><strong><?= $memories_count ?></strong> dodanych</p>
            <p><strong><?= $paid_memories ?></strong> opłaconych</p>
            <a href="my_memories.php">Zobacz wszystkie</a>
        </div>

		
		
		
        <div class="dashboard-box">
            <h4>⏳ Wygasające</h4>
            <p>
                <?= $expiring_memories > 0
                    ? "Masz <strong>$expiring_memories</strong> wspomn." 
                    : "Brak wygasających wspomnień" ?>
            </p>
            <a href="my_memories.php">Sprawdź</a>
        </div>

        <div class="dashboard-box">
            <h4>🕘 Ostatnie logowanie</h4>
            <p><?= $last_login ? formatDateTime($last_login) : 'Brak danych' ?></p>
        </div>

        <div class="dashboard-box">
            <h4>🧠 Dodaj nowe wspomnienie</h4>
            <p>Stwórz osobiste lub rodzinne wspomnienie</p>
            <a href="create_memory.php" class="btn">➕ Utwórz</a>
        </div>
		
		
       <div class="dashboard-box">
       <?php if ($missing_shipping_data): ?>
    <div class="alert" style="background:#ffdddd;padding:10px;border:1px solid #cc0000;color:#a00000;margin-bottom:20px;">
        ⚠️ Twoje dane do wysyłki są nieuzupełnione. <a href="shipping.php">Uzupełnij je teraz</a>.
    </div>
<?php else: ?>
    <div class="dashboard-box" style="background:#f1f1f1; padding:15px; border:1px solid #ccc;">
        <h4>📬 Twój adres wysyłki</h4>
        <p><?= htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name']) ?></p>
        <?php if (!empty($shipping['company'])): ?>
            <p><?= htmlspecialchars($shipping['company']) ?></p>
        <?php endif; ?>
        <?php if (!empty($shipping['nip'])): ?>
            <p>NIP: <?= htmlspecialchars($shipping['nip']) ?></p>
        <?php endif; ?>
        <p><?= htmlspecialchars($shipping['street']) ?></p>
        <p><?= htmlspecialchars($shipping['postal_code'] . ' ' . $shipping['city']) ?></p>
        <p>📞 <?= htmlspecialchars($shipping['phone']) ?></p>
        <p>✉️ <?= htmlspecialchars($shipping['email']) ?></p>
        <?php if (!empty($shipping['note'])): ?>
            <p><em><?= nl2br(htmlspecialchars($shipping['note'])) ?></em></p>
        <?php endif; ?>
        <p><a href="shipping.php">✏️ Edytuj dane</a></p>
    </div>
<?php endif; ?>


        </div>		
		
		
    </div>

    <hr>

    <h4>⚙️ Szybki dostęp:</h4>
    <ul>
        <li><a href="create_memory.php">➕ Utwórz wspomnienie</a></li>
        <li><a href="my_memories.php">📂 Twoje wspomnienia</a></li>
        <li><a href="shipping.php">📬 Dane do wysyłki</a></li>
        <li><a href="profile.php">👤 Profil i hasło</a></li>
    </ul>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
