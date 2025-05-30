<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$msg = '';
$err = '';
$uploadDir = 'uploads/';
$data = [];
$gallery = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 1; $i <= 2; $i++) {
        $first = clean($_POST["first_name_$i"]);
        $last  = clean($_POST["last_name_$i"]);
        $birth = $_POST["birth_date_$i"];
        $death = $_POST["death_date_$i"];
        $bio   = clean($_POST["bio_$i"]);
        $main  = $_FILES["main_photo_$i"];

        if (!$first || !$last || !$birth || !$death || !$bio || $main['error'] !== 0) {
            $err = "❌ Brakuje danych osoby $i lub zdjęcia.";
            break;
        }

        // Weryfikacja typu MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($main['type'], $allowedTypes)) {
            $err = "❌ Zdjęcie osoby $i musi być JPG, PNG lub WEBP.";
            break;
        }

        // Upload zdjęcia głównego
        $mainName = uniqid() . '_' . basename($main['name']);
        if (!move_uploaded_file($main['tmp_name'], $uploadDir . $mainName)) {
            $err = "❌ Nie udało się zapisać zdjęcia osoby $i.";
            break;
        }

        $data[] = [
            'first_name' => $first,
            'last_name'  => $last,
            'birth_date' => $birth,
            'death_date' => $death,
            'bio'        => $bio,
            'main_photo' => $uploadDir . $mainName
        ];
    }

    // Galeria zdjęć – max 10 plików
    if (!$err && !empty($_FILES['gallery']['name'][0])) {
        $totalGallery = count($_FILES['gallery']['name']);
        if ($totalGallery > 10) {
            $err = "❌ Można dodać maksymalnie 10 zdjęć do galerii.";
        } else {
            foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
                if ($_FILES['gallery']['error'][$i] === 0) {
                    $mime = mime_content_type($tmp);
                    if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                        $filename = uniqid() . '_' . basename($_FILES['gallery']['name'][$i]);
                        move_uploaded_file($tmp, $uploadDir . $filename);
                        $gallery[] = $uploadDir . $filename;
                    }
                }
            }
        }
    }

    // Zapis do bazy
    if (!$err && count($data) === 2) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $count = $stmt->fetchColumn() + 1;
        $memory_number = $_SESSION['user']['client_number'] . '/' . $count;

        $stmt = $pdo->prepare("INSERT INTO memories (user_id, memory_number, type, data, gallery) VALUES (?, ?, 'family', ?, ?)");
        $stmt->execute([
            $_SESSION['user']['id'],
            $memory_number,
            json_encode($data),
            json_encode($gallery)
        ]);

        header("Location: loading.php?redirect=my_memories.php&msg=Wspomnienie+jest+zapisane...&delay=1");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj wspomnienie rodzinne</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Dodaj wspomnienie rodzinne</h2>

    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <h3>Osoba 1</h3>
        <input type="text" name="first_name_1" placeholder="Imię" required>
        <input type="text" name="last_name_1" placeholder="Nazwisko" required>
        <input type="date" name="birth_date_1" required>
        <input type="date" name="death_date_1" required>
        <textarea name="bio_1" placeholder="Biografia" required></textarea>
        <label>Zdjęcie główne:</label>
        <input type="file" name="main_photo_1" accept="image/*" required>

        <h3>Osoba 2</h3>
        <input type="text" name="first_name_2" placeholder="Imię" required>
        <input type="text" name="last_name_2" placeholder="Nazwisko" required>
        <input type="date" name="birth_date_2" required>
        <input type="date" name="death_date_2" required>
        <textarea name="bio_2" placeholder="Biografia" required></textarea>
        <label>Zdjęcie główne:</label>
        <input type="file" name="main_photo_2" accept="image/*" required>

        <label>Galeria zdjęć (maks. 10):</label>
        <input type="file" name="gallery[]" accept="image/*" multiple
               onchange="if(this.files.length > 10){ alert('Maksymalnie 10 zdjęć!'); this.value = ''; }">



        <button type="submit">Dodaj wspomnienie</button>
    </form>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
