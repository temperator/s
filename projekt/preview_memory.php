<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Błędne ID.");
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$memory = $stmt->fetch();

if (!$memory) {
    die("❌ Wspomnienie nie istnieje lub nie należy do Ciebie.");
}

$data = json_decode($memory['data'], true);
$gallery = json_decode($memory['gallery'], true);
$type = $memory['type'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podgląd wspomnienia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Podgląd wspomnienia <?= htmlspecialchars($memory['memory_number']) ?></h2>

    <?php foreach ($data as $i => $person): ?>
        <section style="margin-bottom: 40px; border-bottom: 1px solid #ccc;">
            <h3>Osoba <?= $type === 'family' ? ($i + 1) : '' ?></h3>
            <img src="<?= htmlspecialchars($person['main_photo']) ?>" alt="Zdjęcie główne" style="max-height: 100px;"><br>
            <strong>Imię i nazwisko:</strong> <?= htmlspecialchars($person['first_name']) ?> <?= htmlspecialchars($person['last_name']) ?><br>
            <strong>Data urodzenia:</strong> <?= htmlspecialchars($person['birth_date']) ?><br>
            <strong>Data śmierci:</strong> <?= htmlspecialchars($person['death_date']) ?><br>
            <strong>Biografia:</strong><br>
            <p><?= nl2br(htmlspecialchars($person['bio'])) ?></p>
        </section>
    <?php endforeach; ?>

    <?php if ($gallery && is_array($gallery)): ?>
        <h3>Galeria</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach ($gallery as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" style="height: 100px; border: 1px solid #ccc;">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="my_memories.php">← Powrót do wspomnień</a>
    </p>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
