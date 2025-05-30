<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$filterSql = '';
$params = [];

if ($search) {
    $filterSql = "WHERE u.email LIKE :search OR l.ip_address LIKE :search";
    $params[':search'] = "%$search%";
}

// Zliczanie logów
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM logins l
    JOIN users u ON l.user_id = u.id
    $filterSql
");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Pobieranie danych z flagą (JOIN z visitors)
$sql = "
    SELECT l.*, u.email, v.country, v.city
    FROM logins l
    JOIN users u ON l.user_id = u.id
    LEFT JOIN visitors v ON v.ip = l.ip_address
    $filterSql
    ORDER BY l.login_date DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flagi Unicode na podstawie country code
function countryFlag($country) {
    if (!$country) return '';
    $code = strtoupper(substr($country, 0, 2));
    return implode('', array_map(
        fn($c) => mb_chr(ord($c) + 127397, 'UTF-8'),
        str_split($code)
    ));
}





$loginPoints = $pdo->query("SELECT lat, lon, country, city, ip FROM logins WHERE lat IS NOT NULL AND lon IS NOT NULL ORDER BY login_date DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include 'header.php'; ?>




<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>#map { height: 400px; width: 100%; border-radius: 8px; }</style>


 
 




<div class="container mt-5">

	<h4 class="mt-5">🌍 Mapa logowań</h4>
<div id="map" class="my-4"></div>
	  <h3 class="mb-4">🌍 Historia logowań</h3>
	
    <form method="get" class="mb-4 d-flex justify-content-end">
        <input type="text" name="search" class="form-control w-25 me-2" value="<?= htmlspecialchars($search) ?>" placeholder="Szukaj email / IP">
        <button class="btn btn-primary">Szukaj</button>
    </form>

    <?php if (empty($logins)): ?>
        <div class="alert alert-warning">Brak wyników logowań dla podanych kryteriów.</div>
    <?php else: ?>
	  <div class="table-responsive">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Użytkownik</th>
                    <th>Data logowania</th>
                    <th>IP</th>
                    <th>Przeglądarka</th>
                    <th>Kraj / Miasto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logins as $i => $row): ?>
                    <tr>
                        <td><?= $offset + $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= date('d.m.Y H:i:s', strtotime($row['login_date'])) ?></td>
                        <td><?= htmlspecialchars($row['ip_address'] ?? '–') ?></td>
                        <td>
                            <span class="text-muted" style="font-size: 0.9em">
                                <?= htmlspecialchars(substr($row['user_agent'], 0, 80)) ?><?= strlen($row['user_agent']) > 80 ? '...' : '' ?>
                            </span>
                        </td>
                        <td>
 <?= countryFlag($row['country']) ?> <?= htmlspecialchars($row['country'] ?: '–') ?> <?= htmlspecialchars($row['city'] ?: '') ?>
							
							
 		
							
							
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
</div>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<script>
const map = L.map('map').setView([20, 0], 2); // globalny zoom
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18
}).addTo(map);

const points = <?= json_encode($loginPoints) ?>;
points.forEach(p => {
    if (p.lat && p.lon) {
        L.marker([p.lat, p.lon]).addTo(map)
         .bindPopup(`<strong>${p.country}</strong><br>${p.city}<br><small>${p.ip}</small>`);
    }
});
</script>








<?php include 'footer.php'; ?>
