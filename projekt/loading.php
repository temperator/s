<?php
// Ustaw docelow¹ stronê
$redirect = $_GET['redirect'] ?? 'my_memories.php';
$delay = $_GET['delay'] ?? 2;
$message = $_GET['msg'] ?? 'Trwa dodawanie wspomnienia...';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodawanie wspomnienia</title>
    <meta http-equiv="refresh" content="<?= htmlspecialchars($delay) ?>;URL='<?= htmlspecialchars($redirect) ?>'">
    <link rel="stylesheet" href="style.css">
   
</head>
<body>
 <style>
        .loading {
            margin-top: 100px;
            text-align: center;
            font-size: 20px;
        }
        .spinner {
            margin: 30px auto;
            border: 5px solid #ccc;
            border-top: 5px solid #000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% {transform: rotate(0deg);}
            100% {transform: rotate(360deg);}
        }
    </style>

<?php include 'header.php'; ?>


<div class="loading">
    <div class="spinner"></div>
    <p><?= htmlspecialchars($message) ?></p>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
