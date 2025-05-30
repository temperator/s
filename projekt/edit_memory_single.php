<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("❌ Nieprawidłowe ID.");
}

$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$memory = $stmt->fetch();

if (!$memory) {
    die("❌ Brak dostępu lub wspomnienie nie istnieje.");
}

$data = json_decode($memory['data'], true);
$gallery = json_decode($memory['gallery'], true) ?? [];
$person = $data[0]; // tylko 1 osoba w wersji SINGLE
$uploadDir = 'uploads/';
$msg = '';
$err = '';

$maxGallery = 10;
$remaining = $maxGallery - count($gallery);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = clean($_POST['first_name']);
    $last_name = clean($_POST['last_name']);
    $birth_date = $_POST['birth_date'];
    $death_date = $_POST['death_date'];
    $bio = clean($_POST['bio']);
    $new_main_photo = $_FILES['main_photo'];

    // Usuń stare zdjęcie główne jeśli zaznaczono
    if (isset($_POST['delete_main_photo']) && file_exists($person['main_photo'])) {
        unlink($person['main_photo']);
        $person['main_photo'] = '';
    }

    // Dodanie nowego zdjęcia głównego
    if ($new_main_photo['error'] === 0) {
        $mainName = uniqid() . '_' . basename($new_main_photo['name']);
        move_uploaded_file($new_main_photo['tmp_name'], $uploadDir . $mainName);
        $person['main_photo'] = $uploadDir . $mainName;
    }

    // Edycja galerii: usuwanie
    if (isset($_POST['remove_gallery']) && is_array($_POST['remove_gallery'])) {
        foreach ($_POST['remove_gallery'] as $img) {
            if (file_exists($img)) unlink($img);
            $gallery = array_filter($gallery, fn($g) => $g !== $img);
        }
    }

// Dodanie nowych zdjęć do galerii
if (!empty($_FILES['gallery']['name'][0])) {
    $newFilesCount = count($_FILES['gallery']['name']);
    if ($newFilesCount > $remaining) {
        $err = "❌ Możesz dodać maksymalnie $remaining zdjęć (łącznie do $maxGallery).";
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
            if ($_FILES['gallery']['error'][$i] === 0) {
                $mime = mime_content_type($tmp);
                if (in_array($mime, $allowedTypes)) {
                    $filename = uniqid() . '_' . basename($_FILES['gallery']['name'][$i]);
                    move_uploaded_file($tmp, $uploadDir . $filename);
                    $gallery[] = $uploadDir . $filename;
                }
            }
        }
    }
}


    // Zapis danych
    $person['first_name'] = $first_name;
    $person['last_name'] = $last_name;
    $person['birth_date'] = $birth_date;
    $person['death_date'] = $death_date;
    $person['bio'] = $bio;

    $newData = [$person];

    $stmt = $pdo->prepare("UPDATE memories SET data = ?, gallery = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        json_encode($newData),
        json_encode($gallery),
        $id,
        $_SESSION['user']['id']
    ]);

    $msg = "✅ Wspomnienie zaktualizowane.";
       header("Location: loading.php?redirect=my_memories.php&msg=Wspomnienie+jest+zapisane...&delay=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja wspomnienia</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .img-thumb {
            height: 80px;
            border: 1px solid #ccc;
            margin: 5px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Edycja wspomnienia</h2>

    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="first_name" value="<?= htmlspecialchars($person['first_name']) ?>" required>
        <input type="text" name="last_name" value="<?= htmlspecialchars($person['last_name']) ?>" required>
        <input type="date" name="birth_date" value="<?= htmlspecialchars($person['birth_date']) ?>" required>
        <input type="date" name="death_date" value="<?= htmlspecialchars($person['death_date']) ?>" required>
        <textarea name="bio" required><?= htmlspecialchars($person['bio']) ?></textarea>

        <h4>Zdjęcie główne:</h4>
        <?php if (!empty($person['main_photo']) && file_exists($person['main_photo'])): ?>
            <img src="<?= $person['main_photo'] ?>" class="img-thumb"><br>
            <label><input type="checkbox" name="delete_main_photo" value="1"> Usuń aktualne zdjęcie</label><br>
        <?php endif; ?>
        <input type="file" name="main_photo" accept="image/*">

        <h4>Galeria (aktualna):</h4>
        <?php if ($gallery): ?>
            <div style="display:flex; flex-wrap:wrap;">
                <?php foreach ($gallery as $img): ?>
                    <div>
                        <img src="<?= $img ?>" class="img-thumb"><br>
                        <label><input type="checkbox" name="remove_gallery[]" value="<?= $img ?>"> Usuń</label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Brak zdjęć w galerii.</p>
        <?php endif; ?>

      <h4>Dodaj nowe zdjęcia do galerii (pozostało <?= $remaining ?> z 10):</h4>
<input type="file" name="gallery[]" accept="image/*" multiple
       onchange="if(this.files.length > <?= $remaining ?>){ alert('Możesz dodać maksymalnie <?= $remaining ?> zdjęć!'); this.value=''; }">


        <button type="submit">Zapisz zmiany</button>
    </form>
	<p style="margin-top: 30px;">
        <a href="my_memories.php">← Powrót do wspomnień</a>
    </p>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
