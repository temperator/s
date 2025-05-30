<?php
session_start();
require_once "inc/config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj wspomnienie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Wybierz rodzaj wspomnienia</h2>

    <div style="text-align: center; margin-top: 40px;">
        <a href="create_memory_single.php">
            <button style="padding: 15px 30px; font-size: 16px;">🧍‍♂️ Pojedyncze wspomnienie</button>
        </a>

        <a href="create_memory_family.php" style="margin-left: 20px;">
            <button style="padding: 15px 30px; font-size: 16px;">👨‍👩‍👧‍👦 Rodzinne wspomnienie</button>
        </a>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
