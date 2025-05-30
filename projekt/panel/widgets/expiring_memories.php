<?php
$expiringSoon = $pdo->query("
    SELECT m.memory_number, u.email, p.paid_at, m.id
    FROM memories m
    JOIN payments p ON m.id = p.memory_id
    JOIN users u ON m.user_id = u.id
    WHERE p.status = 'paid' 
    AND DATE_ADD(p.paid_at, INTERVAL 1780 DAY) < NOW() + INTERVAL 60 DAY
    ORDER BY p.paid_at ASC
    LIMIT 5
")->fetchAll();
?>

  <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
    <div class="card-body">
        <h5 class="card-title">⏰ Wygasające wspomnienia</h5>
        <?php if ($expiringSoon): ?>
            <ul class="list-group">
                <?php foreach ($expiringSoon as $row): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        #<?= $row['memory_number'] ?> – <?= htmlspecialchars($row['email']) ?>
                        <span class="badge bg-warning text-dark">Wygasa wkrótce</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">Brak wygasających wspomnień w najbliższych 60 dniach.</p>
        <?php endif; ?>
    </div>
</div>
</div>