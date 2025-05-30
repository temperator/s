<?php
$active_today = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM logins WHERE DATE(login_date) = CURDATE()")->fetchColumn();
$ghost_users = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login IS NULL")->fetchColumn();
$users_without_memories = $pdo->query("SELECT COUNT(*) FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM memories)")->fetchColumn();
?>


  
  
 
  <div class="col-md-4 mb-4">
    <div class="card border-success text-center">
      <div class="card-body">
        <div class="card-title">🟢 Aktywni dziś</div>
        <div class="metric text-success"><?= $active_today ?></div>
      </div>
    </div>
  </div>
 <div class="col-md-4 mb-4">
    <div class="card border-warning text-center">
      <div class="card-body">
        <div class="card-title">👻 Bez logowań</div>
        <div class="metric text-warning"><?= $ghost_users ?></div>
      </div>
    </div>
  </div>
 <div class="col-md-4 mb-4">
    <div class="card border-dark text-center">
      <div class="card-body">
        <div class="card-title">❌ Bez wspomnień</div>
        <div class="metric text-dark"><?= $users_without_memories ?></div>
      </div>
    </div>
  </div>
 
