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

$stmt = $pdo->prepare("SELECT * FROM memories WHERE id = ? AND user_id = ? AND type = 'family'");
$stmt->execute([$id, $_SESSION['user']['id']]);
$memory = $stmt->fetch();

if (!$memory) {
    die("❌ Wspomnienie nie istnieje lub nie należy do Ciebie.");
}

$data = json_decode($memory['data'], true);
$gallery = json_decode($memory['gallery'], true) ?? [];
$uploadDir = 'uploads/';
$msg = '';
$err = '';

$maxGallery = 10;
$remaining = $maxGallery - count($gallery);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i <= 1; $i++) {
        $data[$i]['first_name'] = clean($_POST["first_name_$i"]);
        $data[$i]['last_name'] = clean($_POST["last_name_$i"]);
        $data[$i]['birth_date'] = $_POST["birth_date_$i"];
        $data[$i]['death_date'] = $_POST["death_date_$i"];
        $data[$i]['bio'] = clean($_POST["bio_$i"]);

        // Usuń zdjęcie główne
        if (isset($_POST["delete_main_$i"]) && file_exists($data[$i]['main_photo'])) {
            unlink($data[$i]['main_photo']);
            $data[$i]['main_photo'] = '';
        }

        // Dodaj nowe zdjęcie główne
        if ($_FILES["main_photo_$i"]['error'] === 0) {
            $mainName = uniqid() . '_' . basename($_FILES["main_photo_$i"]['name']);
            move_uploaded_file($_FILES["main_photo_$i"]['tmp_name'], $uploadDir . $mainName);
            $data[$i]['main_photo'] = $uploadDir . $mainName;
        }
    }

    // Usuń zdjęcia z galerii
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


    // Zapis
    $stmt = $pdo->prepare("UPDATE memories SET data = ?, gallery = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        json_encode($data),
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
    <title>Edycja wspomnienia rodzinnego</title>
    <link rel="stylesheet" href="style.css">
    <style>.img-thumb { height: 80px; border: 1px solid #ccc; margin: 5px; }</style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h2>Edycja wspomnienia rodzinnego</h2>
    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php foreach ($data as $i => $person): ?>
            <h3>Osoba <?= $i + 1 ?></h3>
            <input type="text" name="first_name_<?= $i ?>" value="<?= htmlspecialchars($person['first_name']) ?>" required>
            <input type="text" name="last_name_<?= $i ?>" value="<?= htmlspecialchars($person['last_name']) ?>" required>
            <input type="date" name="birth_date_<?= $i ?>" value="<?= htmlspecialchars($person['birth_date']) ?>" required>
            <input type="date" name="death_date_<?= $i ?>" value="<?= htmlspecialchars($person['death_date']) ?>" required>
            <textarea name="bio_<?= $i ?>" required><?= htmlspecialchars($person['bio']) ?></textarea>

            <?php if (!empty($person['main_photo']) && file_exists($person['main_photo'])): ?>
                <img src="<?= $person['main_photo'] ?>" class="img-thumb"><br>
                <label><input type="checkbox" name="delete_main_<?= $i ?>" value="1"> Usuń zdjęcie główne</label><br>
            <?php endif; ?>
            <input type="file" name="main_photo_<?= $i ?>" accept="image/*"><br><br>
        <?php endforeach; ?>

        <h4>Galeria:</h4>
        <div style="display:flex; flex-wrap:wrap;">
            <?php foreach ($gallery as $img): ?>
                <div>
                    <img src="<?= $img ?>" class="img-thumb"><br>
                    <label><input type="checkbox" name="remove_gallery[]" value="<?= $img ?>"> Usuń</label>
                </div>
            <?php endforeach; ?>
        </div>

       <h4>Dodaj nowe zdjęcia do galerii (pozostało <?= $remaining ?> z 10):</h4>
<input type="file" name="gallery[]" accept="image/*" multiple
       onchange="if(this.files.length > <?= $remaining ?>){ alert('Możesz dodać maksymalnie <?= $remaining ?> zdjęć!'); this.value=''; }">


        <button type="submit">Zapisz zmiany</button>
    </form>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
