<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

$search = trim($_GET['search'] ?? '');
$params = [];
$whereSql = '';

if ($search !== '') {
    $whereSql = "WHERE email LIKE :s OR ip_address LIKE :s OR user_agent LIKE :s";
    $params[':s'] = '%' . $search . '%';
}

$sql = "SELECT * FROM registration_attempts $whereSql ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "header.php"; ?>

<div class="container mt-5">
    <h3 class="mb-4">🕵️ Logi prób rejestracji</h3>

    <form class="d-flex justify-content-end mb-4" method="get">
        <input type="text" name="search" class="form-control w-25 me-2" placeholder="Szukaj e-mail, IP..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary">Szukaj</button>
    </form>

    <?php if (empty($attempts)): ?>
        <div class="alert alert-info">Brak wyników.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>IP</th>
                        <th>Agent</th>
                        <th>Sukces</th>
                        <th>Bot</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attempts as $r): ?>
                        <tr class="<?= $r['is_bot'] ? 'table-danger' : ($r['success'] ? 'table-success' : '') ?>">
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><?= htmlspecialchars($r['ip_address']) ?></td>
                            <td><small><?= substr(htmlspecialchars($r['user_agent']), 0, 40) ?>...</small></td>
                            <td><?= $r['success'] ? '✅' : '❌' ?></td>
                            <td><?= $r['is_bot'] ? '🤖 TAK' : '—' ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<?php include "footer.php"; ?>
