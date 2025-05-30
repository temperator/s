<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<?php if (!isset($_SESSION)) session_start();  

require_once __DIR__ . '/inc/config.php';
$shipping_alert = false;

if (isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("SELECT * FROM shipping_data WHERE user_id = ?");
    $stmt->execute([$userId]);
    $shipping = $stmt->fetch();

    $shipping_alert = !$shipping || empty($shipping['first_name']) || empty($shipping['last_name']) || empty($shipping['street']) || empty($shipping['postal_code']) || empty($shipping['city']) || empty($shipping['email']);
}


include "track.php";
?>

  <meta name="viewport" content="width=device-width, initial-scale=1">
<header class="user-header">
    <div class="nav-container">
        
        <nav>
		
		
		<?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
    <li><a href="panel/index.php">🔐 Panel Admina</a></li>
<?php endif; ?> 
		
		
		
            <a href="index.php">Główna</a>
            <a href="shoppr.php">Sklep</a>
            <a href="contact.php">Kontakt</a>

            <?php if (isset($_SESSION['user'])): ?>
			
			<a href="create_memory.php">Utwórz wspomnienie</a>
            <a href="my_memories.php">Twoje wspomnienia</a>
            
			
			<a href="shipping.php">
    Dane do wysyłki
    <?php if ($shipping_alert): ?>
        <span class="dot-alert" title="Dane nieuzupełnione"></span>
    <?php endif; ?>
</a>

			
            <a href="profile.php">Ustawienia</a>
			
             
				
				
                <a href="dashboard.php">Twoje konto</a>
				   <span class="welcome">Witaj, <?= htmlspecialchars($_SESSION['user']['first_name']) ?></span>
				
                <a href="logout.php" class="logout">Wyloguj</a>
            <?php else: ?>
                <a href="login.php">Logowanie</a>
                <a href="register.php">Rejestracja</a>
            <?php endif; ?>
			
			
			
			
			 
			
			
			
			

        </nav>
    </div>
</header>
