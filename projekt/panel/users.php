<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [];
$searchSql = '';
if ($search !== '') {
    $searchSql = "WHERE u.first_name LIKE :s OR u.last_name LIKE :s OR u.email LIKE :s OR u.client_number LIKE :s";
    $params[':s'] = '%' . $search . '%';
}

// Liczba użytkowników
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $searchSql");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Pobieranie danych
$dataStmt = $pdo->prepare("
    SELECT u.id, u.client_number, u.first_name, u.last_name, u.email, u.registered_at,
           (SELECT COUNT(*) FROM memories m WHERE m.user_id = u.id) AS memory_count
    FROM users u
    $searchSql
    ORDER BY u.registered_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $dataStmt->bindValue($k, $v);
}
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$users = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "header.php"; ?>

<div class="container my-4">
    <h5>👤 Lista użytkowników</h5>

   

	 <form method="get" class="mb-4 d-flex justify-content-end">
        <input type="text" name="search" class="form-control w-100 me-2" value="<?= htmlspecialchars($search) ?>" placeholder="Szukaj">
        <button class="btn btn-primary">Szukaj</button>
    </form>
	
	
	
	
    <div class="table-responsive">
        <table class="table table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
				
                    <th>Id</th>				
                    <th>Numer</th>
                    <th>Klient</th>
                    <th>Email</th>
                    <th>Rejestracjia</th>
                    <th>Ws.</th>
                    <th>Opcje</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6">❌ Brak wyników.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>						
                            <td><?= htmlspecialchars($u['client_number']) ?></td>
                            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($u['registered_at'])) ?></td>
                            <td><?= (int) $u['memory_count'] ?></td>
                            <td>
                                <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('🗑️ Czy na pewno chcesz usunąć użytkownika <?= htmlspecialchars($u['email']) ?>?')">Usuń</a>
                            </td>
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
                $range = 2;
                $ellipsisShown = false;

                for ($i = 1; $i <= $totalPages; $i++) {
                    if (
                        $i == 1 || 
                        $i == $totalPages || 
                        ($i >= $page - $range && $i <= $page + $range)
                    ) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=" . urlencode($search) . "'>$i</a></li>";
                        $ellipsisShown = false;
                    } elseif (!$ellipsisShown) {
                        echo "<li class='page-item disabled'><span class='page-link'>…</span></li>";
                        $ellipsisShown = true;
                    }
                }
                ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
