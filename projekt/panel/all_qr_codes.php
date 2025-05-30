<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security.php";
requireAdmin();

// Paginacja + filtr
$perPage = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$search = trim($_GET['q'] ?? '');

// Wyszukiwanie
$where = '';
$params = [];
if ($search) {
    $where = "WHERE u.email LIKE ? OR m.memory_number LIKE ? OR u.client_number LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Liczenie wszystkich
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM memories m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN qr_links q ON q.memory_id = m.id
    $where
");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$pages = ceil($total / $perPage);

// Dane
$stmt = $pdo->prepare("
    SELECT m.id, m.memory_number, q.secure_hash, u.email 
    FROM memories m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN qr_links q ON q.memory_id = m.id
    $where
    ORDER BY m.id DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ?? DODAJ TO:
foreach ($results as &$row) {
    if (!$row['secure_hash']) {
        $secure_hash = bin2hex(random_bytes(20));
        $pdo->prepare("INSERT INTO qr_links (memory_id, secure_hash) VALUES (?, ?)")
            ->execute([$row['id'], $secure_hash]);
        $row['secure_hash'] = $secure_hash;
    }
}
?>

<?php include "header.php"; ?>

<div class="container my-4 ">
    <h3 class="mb-4">📡 Lista kodów QR</h3>

    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Szukaj po email / numerze..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary">🔍 Szukaj</button>
        </div>
    </form>

    	
   <div class="d-flex flex-wrap justify-content-start  gap-5 ">
         
        <?php foreach ($results as $row): ?>
            <?php
                $link = "https://dfde.webd.pro/" . $row['secure_hash'];
                require_once "inc/phpqrcode/qrlib.php";
                ob_start();
                QRcode::png($link, null, QR_ECLEVEL_L, 12, 1); // <- rozmiar i jakość
                $qrBase64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());
            ?>
            <div class="card position-relative shadow" style="width: 197px;">
                <div class="qr-blur position-relative">
                    <img src="<?= $qrBase64 ?>" alt="QR" class="qr-img img-fluid" style="opacity:0.2;">
                    <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                         style="background-color:rgba(255,255,255,0.8); transition: 0.3s;">
                        👁️ Najedź
                    </div>
                </div>
                <div class="card-body p-2 text-center">
                    <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small><br>
                    <strong><?= htmlspecialchars($row['memory_number']) ?></strong>
                    <div class="mt-2">
                        <a download="<?= $row['memory_number'] ?>.png" href="<?= $qrBase64 ?>" class="btn btn-sm btn-outline-dark w-100">⬇️</a>
                        <a href="<?= $link ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 mt-1">🔗</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&q=<?= urlencode($search) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?> 

<style>
.qr-blur:hover img {
    opacity: 1 !important;
}
.qr-blur:hover .overlay {
    opacity: 0 !important;
}
.qr-img {
    max-width: 100%;
    height: auto;
    transition: 0.3s;
}
.overlay {
    opacity: 1;
    pointer-events: none;
}

.card {
    position: relative;
    background: white;
    border: 1px solid #ccc;
    padding: 0px; 
    text-align: center;
 
	 
}
 


</style>