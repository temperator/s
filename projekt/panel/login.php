<?php
require_once "inc/config.php";
require_once "inc/security.php";
require_once "inc/functions.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'email' => $admin['email']
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = "Nieprawidłowy email lub hasło.";
    }
}


?>

<!DOCTYPE html>
<html lang="pl">
<head>




    <meta charset="UTF-8">
    <title>Logowanie do panelu admina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"  media="all" rel="stylesheet">
    <style>
	
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        body {
            background-color: #fff;
          <!--  font-family: 'Segoe UI', sans-serif;
			font-family: monospace ;
			 -->
		 
  
  
  font-family: "Roboto", Arial, serif;
        }
        .login-box {
            max-width: 420px;
            margin: 80px auto;
            padding: 30px;
			 
   
            background: #ffffff;
            border-radius: 0px;
            box-shadow: 0 0 0px rgba(0,0,0,0.00);
        }
    </style>
	
	
</head>
<body>

<div class="login-box">
    <h4 class="text-center mb-4">🔐 Panel Administratora</h4>

	
	
	
	
 
	
	
	
	
	
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" class="form-control" name="email" required autofocus>
        </div>

        <div class="mb-3">
    <label for="password" class="form-label">Hasło:</label>
    <div class="input-group">
        <input type="password" class="form-control" name="password" id="passwordInput" required>
        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
            👁️
        </button>
    </div>
</div>


        <button class="btn btn-primary w-100" type="submit">Zaloguj się</button>
    </form>

    <div class="text-center mt-3 text-muted" style="font-size: 0.9em;">
        &copy; <?= date('Y') ?> Admin Panel<br>
		admin@admin.pl     <br>    admin
		
    </div>
</div>
<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordInput = document.getElementById("passwordInput");
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    this.textContent = type === "password" ? "👁️" : "🙈";
});
</script>

</body>
</html>
