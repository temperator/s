<?php
$logs = $pdo->query("
    SELECT email, ip_address, user_agent, success, is_bot, created_at
    FROM registration_attempts
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll();
?>

  <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
    <div class="card-body">
        <h5 class="card-title">🛡️ Logi prób rejestracji</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Email</th>
						 <th>Data</th>
						
                        <th>IP</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Bot</th>
                       
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr class="<?= $log['success'] ? 'table-success' : 'table-danger' ?>">
					
                        <td><?= htmlspecialchars($log['email']) ?></td>
						 <td><?= $log['created_at'] ?></td>
						
                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
						
						
                        <td title="<?= htmlspecialchars($log['user_agent']) ?>"><?= substr($log['user_agent'], 0, 20) ?>...</td>
                        <td><?= $log['success'] ? '✅' : '❌' ?></td>
						
						
                        <td><?= $log['is_bot'] ? '🤖' : '🧍' ?></td>
						
						
                       
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>