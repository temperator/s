<?php
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_memories = $pdo->query("SELECT COUNT(*) FROM memories")->fetchColumn();
$paid_memories = $pdo->query("SELECT COUNT(*) FROM memories WHERE is_paid = 1")->fetchColumn();
$unpaid_memories = $total_memories - $paid_memories;
$total_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$total_logins = $pdo->query("SELECT COUNT(*) FROM logins")->fetchColumn();
?>

 

 

 
    
   
 


 <div class="col-md-3 mb-4">
  <div class="card border-primary text-center">
    <div class="card-body">
      <div class="card-title">👥 Użytkownicy</div>
      <div class="metric text-primary"><?= $total_users ?></div>
    </div>
  </div>
</div>
 <div class="col-md-3 mb-4">
  <div class="card border-info text-center">
    <div class="card-body">
      <div class="card-title">🧠 Wspomnienia</div>
      <div class="metric text-info"><?= $total_memories ?></div>
    </div>
  </div>
</div>
<div class="col-md-3 mb-4">
  <div class="card border-success text-center">
    <div class="card-body">
      <div class="card-title">✅ Opłacone</div>
      <div class="metric text-success"><?= $paid_memories ?></div>
    </div>
  </div>
</div>
<div class="col-md-3 mb-4">
  <div class="card border-danger text-center">
    <div class="card-body">
      <div class="card-title">❌ Nieopłacone</div>
      <div class="metric text-danger"><?= $unpaid_memories ?></div>
    </div>
  </div>
</div>
