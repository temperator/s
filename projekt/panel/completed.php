<?php
require_once "inc/config.php";
require_once "inc/security.php";
require_once "inc/functions.php";

requireAdmin();

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$params = [];
$where = "WHERE m.status = 'zrealizowano'";

if ($search !== '') {
    $where .= " AND (u.email LIKE :s OR m.memory_number LIKE :s)";
    $params[':s'] = '%' . $search . '%';
}

// Liczenie łącznej ilości
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM memories m
    LEFT JOIN users u ON m.user_id = u.id
    $where
");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Pobieranie danych
$stmt = $pdo->prepare("
    SELECT 
        m.id, m.memory_number, m.created_at, m.status,
        p.paid_at, p.amount, p.method,
        q.secure_hash, q.view_count,
        u.client_number, u.email
    FROM memories m
    LEFT JOIN payments p ON p.memory_id = m.id
    LEFT JOIN qr_links q ON q.memory_id = m.id
    LEFT JOIN users u ON u.id = m.user_id
    $where
    ORDER BY m.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "header.php"; ?>

<div class="container my-4">
    <h4>✅ Zrealizowane zamówienia</h4>

    <form method="get" class="mb-3 d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Szukaj po emailu lub numerze wspomnienia" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary">🔍 Szukaj</button>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Id</th>
                    <th>Wspomnienie</th>
                    <th>Email</th>
                    <th>Zamówiono</th>
                    <th>Realizacja</th>
                    <th>Ważne do</th>
                    <th>Płatność</th>
                    <th>Kwota</th>
                  <!--  <th>QR</th> -->
                    <th>Wejścia</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="text-danger">❌ Brak wyników.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $paidAt = $r['paid_at'] ? date('d.m.Y H:i', strtotime($r['paid_at'])) : '—';
                        $expire = $r['paid_at'] ? date('d.m.Y', strtotime($r['paid_at'] . ' +1835 days')) : '—';
                        $num = htmlspecialchars($r['memory_number']);
                        $parts = explode('/', $num);
                        $numFormatted = "<strong>{$parts[0]}/{$parts[1]}</strong>/{$parts[2]}";
                        ?>
                        <tr>
                            <td><?= $r['id'] ?></td>						
                            <td> <a href="view_order.php?id=<?= $r['id'] ?>"> <?= $numFormatted ?></a></td>
                            <td><a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a></td>
                            <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                            <td><?= $paidAt ?></td>
                            <td><?= $expire ?></td>
                            <td><?= htmlspecialchars($r['method'] ?: '—') ?></td>
                            <td><?= number_format($r['amount'], 2, ',', ' ') ?> €</td>
                          <!--  <td>
                                < ?php if ($r['secure_hash']): ?>
                                    <img src="qr_image.php?code=< ?= urlencode($r['secure_hash']) ?>" style="max-width:40px">
                                < ?php else: ?>
                                    ❌
                                < ?php endif; ?>
                            </td>-->
                            <td><?= $r['view_count'] ?? 0 ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "&search=" . urlencode($search) . "'>‹‹‹</a></li>";
                }

                $range = 2;
                $ellipsis = false;
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)) {
                        $active = $i == $page ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=" . urlencode($search) . "'>$i</a></li>";
                        $ellipsis = false;
                    } elseif (!$ellipsis) {
                        echo "<li class='page-item disabled'><span class='page-link'>…</span></li>";
                        $ellipsis = true;
                    }
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "&search=" . urlencode($search) . "'>›››</a></li>";
                }
                ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
