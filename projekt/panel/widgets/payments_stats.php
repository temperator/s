<?php
$avg_payment = number_format($pdo->query("SELECT AVG(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?? 0, 2, '.', ' ');
$max_payment = number_format($pdo->query("SELECT MAX(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?? 0, 2, '.', ' ');
$total_amount = number_format($pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?? 0, 2, '.', ' ');
?>







 <div class="col-md-4 mb-4">
    <div class="card border-success text-center">
      <div class="card-body">
        <div class="card-title">💶 Średnia wpłata</div>
        <div class="metric text-secondary"><?= $avg_payment ?> €</div>
      </div>
    </div>
  </div>
  
 <div class="col-md-4 mb-4">
    <div class="card border-success text-center">
      <div class="card-body">
        <div class="card-title">💰 Największa wpłata</div>
        <div class="metric text-primary"><?= $max_payment ?> €</div>
      </div>
    </div>
  </div>

 <div class="col-md-4 mb-4">
    <div class="card border-success text-center">
      <div class="card-body">
        <div class="card-title">💳 Łączna suma</div>
        <div class="metric text-success"><?= $total_amount ?> €</div>
      </div>
    </div>
  </div>

