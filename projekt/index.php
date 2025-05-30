<?php
session_start();

?>












<!DOCTYPE html>
<html lang="pl">  
<head>
    <meta charset="UTF-8">
    <title>Digital Cemetery – Strona główna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h1>Witamy na stronie Digital Cemetery</h1>
    <p>
        Nasz portal umożliwia tworzenie i upamiętnianie bliskich zmarłych w formie cyfrowej.<br>
        Każdy zarejestrowany użytkownik może stworzyć wspomnienie – pojedyncze lub rodzinne – dodać zdjęcia, biografię oraz aktywować kod QR.
    </p>

    <p>
        Po założeniu konta otrzymujesz swój unikalny numer klienta, który służy do identyfikacji wspomnień i zamówień.
    </p>

    <?php if (!isset($_SESSION['user'])): ?>
        <p><a href="register.php" class="btn">Zarejestruj się</a> lub <a href="login.php">Zaloguj się</a>, aby rozpocząć.</p>
    <?php else: ?>
        <p>Miło Cię widzieć ponownie, <?= htmlspecialchars($_SESSION['user']['first_name']) ?>!</p>
        <p><a href="dashboard.php">Przejdź do panelu</a></p>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
