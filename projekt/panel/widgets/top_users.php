<?php
require_once "inc/config.php";
$topUsers = $pdo->query("
    SELECT u.email, COUNT(p.id) as orders
    FROM users u
    JOIN payments p ON p.user_id = u.id
    WHERE p.status = 'completed'
    GROUP BY u.id
    ORDER BY orders DESC
    LIMIT 5
")->fetchAll();


// Aktywność użytkowników
$active_today = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM logins WHERE DATE(login_date) = CURDATE()")->fetchColumn();
$ghost_users = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login IS NULL")->fetchColumn();
$top_logins = $pdo->query("SELECT user_id, COUNT(*) as count FROM logins GROUP BY user_id ORDER BY count DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$top_user_email = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$top_user_email->execute([$top_logins['user_id']]);
$top_user = $top_user_email->fetchColumn();

// Użytkownicy
$newest_user = $pdo->query("SELECT email FROM users ORDER BY registered_at DESC LIMIT 1")->fetchColumn();
$users_without_memories = $pdo->query("SELECT COUNT(*) FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM memories)")->fetchColumn();
?>

   
        <div class="col-md-6 mb-4">
            <div class="card border-success text-center">
                  <div class="card-body">
                    <h5 class="card-title">👑 Najczęściej logujący się użytkownik</h5>
                    <p class="display-8 text-success"><?= htmlspecialchars($top_user) ?> (<?= $top_logins['count'] ?> logowań)</p>
               
            </div>
        </div>
 </div>
 
        <div class="col-md-6 mb-4">
            <div class="card border-danger text-center">
                 <div class="card-body">
                    <h5 class="card-title">👥 Nowy użytkownik</h5>
					
                    <p class="display-8 text-danger"><?= htmlspecialchars($newest_user) ?></p>
               
            </div>
        </div>
     </div>

	
	


  <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
    <div class="card-body">
        <h5 class="card-title">👑 Najbardziej aktywni klienci</h5>
        <ul class="list-group">
		
		 
		
            <?php foreach ($topUsers as $user): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($user['email']) ?>
                    <span class="badge bg-success"><?= $user['orders'] ?> zamówień</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</div>


