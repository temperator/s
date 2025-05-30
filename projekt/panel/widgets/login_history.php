<?php
$logins = $pdo->query("
    SELECT l.user_id, u.email, l.login_date, l.ip_address, l.user_agent 
    FROM logins l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.login_date DESC
    LIMIT 10
")->fetchAll();
?>
  
  <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
    <div class="card-body">
        <h5 class="card-title">🔑 Historia logowań</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle text-center">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Data</th>
                        <th>IP</th>  
                        <th>Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logins as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['email']) ?></td>
                            <td><?= $entry['login_date'] ?></td>
                            <td><?= $entry['ip_address'] ?></td>
                            <td title="<?= htmlspecialchars($entry['user_agent']) ?>"><?= substr($entry['user_agent'], 0, 20) ?>...</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>