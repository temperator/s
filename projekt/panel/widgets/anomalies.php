<?php
// IP z wieloma rejestracjami
$anomalyIps = $pdo->query("
    SELECT ip_address, COUNT(*) as count 
    FROM registration_attempts 
    WHERE success = 1 
    GROUP BY ip_address 
    HAVING count > 3 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll();
?>

  <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
    <div class="card-body">
        <h5 class="card-title">🚨 Podejrzane IP (wiele rejestracji)</h5>
        <?php if ($anomalyIps): ?>
            <ul class="list-group">
                <?php foreach ($anomalyIps as $row): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($row['ip_address']) ?>
                        <span class="badge bg-danger"><?= $row['count'] ?> rejestracji</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">Brak podejrzanych aktywności.</p>
        <?php endif; ?>
    </div>
</div>
</div>