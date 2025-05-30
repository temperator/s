<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security.php";
requireAdmin();



$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$params = [];
$searchSql = '';

if ($search !== '') {
    $searchSql = "WHERE u.first_name LIKE :s OR u.last_name LIKE :s OR m.memory_number LIKE :s";
    $params[':s'] = '%' . $search . '%';
}

// LICZBA
$countSql = "SELECT COUNT(*) FROM memories m 
             JOIN users u ON u.id = m.user_id 
             $searchSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// DANE
$sql = "SELECT m.id, m.memory_number, m.type, m.created_at, m.status AS memory_status, m.is_paid,
               u.first_name, u.last_name,
               p.status AS payment_status, p.paid_at,
               s.id AS shipping_id
        FROM memories m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN payments p ON p.memory_id = m.id
        LEFT JOIN shipping_data s ON s.user_id = u.id
        $searchSql
        ORDER BY m.created_at DESC
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$today = new DateTime();
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">📦 Lista zamówień</h3>

    <form method="GET" class="mb-4 d-flex justify-content-end">
        <input type="text" name="search" class="form-control w-25 me-2" value="<?= htmlspecialchars($search) ?>" placeholder="Szukaj klienta, wspomnienia...">
        <button class="btn btn-primary">Szukaj</button>
    </form>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Brak zamówień dla podanych kryteriów.</div>
    <?php else: ?>
	  <div class="table-responsive">
        <table class="table table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Id</th>				
                    <th>Wspomnienie</th>
                    <th>Klient</th>
                    <th>Data</th>
                    <th>Rodzaj</th>
                    <th>Status</th>
                    <th>Opłacone</th>
                    <th>Zostało</th>
                    <th>Adres</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order):
                    $paid = (bool)$order['is_paid'];
                    $paidBadge = $paid ? '✅' : '❌';

                    if ($paid && $order['paid_at']) {
                        $paidDate = new DateTime($order['paid_at']);
                        $endDate = (clone $paidDate)->modify('+1835 days');
                        $diff = $today->diff($endDate)->format('%r%a');

                        if ($diff < 0) {
                            $expiry = '<span class="badge bg-danger">Wygasło</span>';
                        } elseif ($diff <= 20) {
                            $expiry = "<span class='badge bg-warning text-dark'>❗ $diff dni</span>";
                        } else {
                            $expiry = "<span class='badge bg-success'>$diff dni</span>";
                        }
                    } else {
                        $expiry = '–';
                    }

                    $status = strtolower($order['memory_status']);
$rowClass = ($status === 'zrealizowano') ? 'table-success' : 'table-danger';

                    $memoryLink = "view_order.php?id=" . urlencode($order['id']);
                    $displayNumber = htmlspecialchars($order['memory_number']);
                    $typeIcon = $order['type'] === 'single' ? '👤' : '👪';
                    ?>
                    <tr class="<?= $rowClass ?>">
					
					<td><?= $order['id'] ?></td>
                        <td>
						
						
						
						<a href="<?= $memoryLink ?>"><?= $displayNumber ?></a></td>
                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                        <td><?= $typeIcon ?></td>
                        <td><?= ucfirst($order['memory_status']) ?></td>
                        <td><?= $paidBadge ?></td>
                        <td><?= $expiry ?></td>
                        <td><?= $order['shipping_id'] ? '✔️' : '❌' ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
</div>
        <!-- PAGINACJA -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($totalPages > 1):
                    if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">‹‹‹</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">‹‹‹</span></li>
                    <?php endif;

                    $range = 2;
                    $ellipsisShown = false;

                    for ($i = 1; $i <= $totalPages; $i++):
                        if (
                            $i == 1 ||
                            $i == $totalPages ||
                            ($i >= $page - $range && $i <= $page + $range)
                        ):
                            $active = ($i == $page) ? 'active' : '';
                            echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=" . urlencode($search) . "'>$i</a></li>";
                            $ellipsisShown = false;
                        elseif (!$ellipsisShown):
                            echo "<li class='page-item disabled'><span class='page-link'>…</span></li>";
                            $ellipsisShown = true;
                        endif;
                    endfor;

                    if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">›››</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">›››</span></li>
                    <?php endif;
                endif; ?>
            </ul>
        </nav>
    <?php endif ?>
</div>

<?php include 'footer.php'; ?>
