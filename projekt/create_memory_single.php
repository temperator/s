<?php
session_start();
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$err = '';
$msg = '';
$uploadDir = 'uploads/';
$gallery = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = clean($_POST['first_name']);
    $last_name  = clean($_POST['last_name']);
    $birth_date = $_POST['birth_date'];
    $death_date = $_POST['death_date'];
    $bio        = clean($_POST['bio']);
    $main_photo = $_FILES['main_photo'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    // Walidacja podstawowych pól
    if (!$first_name || !$last_name || !$birth_date || !$death_date || !$bio || $main_photo['error'] !== 0) {
        $err = "❌ Wszystkie pola są wymagane, w tym zdjęcie główne.";
    } elseif (!in_array($main_photo['type'], $allowedTypes)) {
        $err = "❌ Zdjęcie główne musi być JPG, PNG lub WEBP.";
    } else {
        // Upload zdjęcia głównego
        $main_name = uniqid() . '_' . basename($main_photo['name']);
        if (!move_uploaded_file($main_photo['tmp_name'], $uploadDir . $main_name)) {
            $err = "❌ Nie udało się przesłać zdjęcia głównego.";
        }

        // Galeria – limit 10 zdjęć
        if (!$err && !empty($_FILES['gallery']['name'][0])) {
            $totalGallery = count($_FILES['gallery']['name']);
            if ($totalGallery > 10) {
                $err = "❌ Maksymalnie 10 zdjęć do galerii.";
            } else {
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

        if (!$err) {
            $data = [[
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'birth_date' => $birth_date,
                'death_date' => $death_date,
                'bio'        => $bio,
                'main_photo' => $uploadDir . $main_name
            ]];

            // Numer wspomnienia
          //  $stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = ?");
        //    $stmt->execute([$_SESSION['user']['id']]);
        //    $count = $stmt->fetchColumn() + 1;
        //   $memory_number = $_SESSION['user']['client_number'] . '/' . $count;
         
 
 
 // Pobierz ostatni numer z memory_number
$stmt = $pdo->prepare("
    SELECT memory_number 
    FROM memories 
    WHERE user_id = ? 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$_SESSION['user']['id']]);
$last = $stmt->fetchColumn();

$nextNumber = 1;

if ($last) {
    $parts = explode('/', $last);
    $lastNumber = intval(end($parts));
    $nextNumber = $lastNumber + 1;
}

$today = date('dmY'); // np. 280525
$memory_number = $_SESSION['user']['client_number'] . '/' . $nextNumber;
 
 
 
 
 
  
 
 
 
 
 
 
 
 


            // Zapis
            $stmt = $pdo->prepare("INSERT INTO memories (user_id, memory_number, type, data, gallery) VALUES (?, ?, 'single', ?, ?)");
            $stmt->execute([
                $_SESSION['user']['id'],
                $memory_number,
                json_encode($data),
                json_encode($gallery)
            ]);

            $msg = "✅ Wspomnienie dodane.";
            header("Location: loading.php?redirect=my_memories.php&msg=Wspomnienie+jest+zapisane...&delay=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj wspomnienie (pojedyncze)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <h2>Dodaj wspomnienie (pojedyncze)</h2>

    <?php if ($err): ?><div class="alert"><?= $err ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="success"><?= $msg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="first_name" placeholder="Imię" required>
        <input type="text" name="last_name" placeholder="Nazwisko" required>
        <input type="date" name="birth_date" required>
        <input type="date" name="death_date" required>
        <textarea name="bio" placeholder="Biografia" required></textarea>

        <label>Zdjęcie główne:</label>
        <input type="file" name="main_photo" accept="image/*" required>

        <label>Galeria zdjęć (maks. 10):</label>
        <input type="file" name="gallery[]" accept="image/*" multiple
               onchange="if(this.files.length > 10){ alert('Maksymalnie 10 zdjęć!'); this.value = ''; }">

        <button type="submit">💾 Zapisz wspomnienie</button>
    </form>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
