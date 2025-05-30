<?php
$points = $pdo->query("
    SELECT lat, lon, country, city, ip 
    FROM visitors 
    ORDER BY visited_at DESC 
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

$topCountries = $pdo->query("
    SELECT country, COUNT(*) as total 
    FROM visitors 
    WHERE country IS NOT NULL AND country != ''
    GROUP BY country 
    ORDER BY total DESC 
    LIMIT 5
")->fetchAll();

$lastIps = $pdo->query("
    SELECT ip, country, city, visited_at 
    FROM visitors 
    ORDER BY visited_at DESC 
    LIMIT 5
")->fetchAll();
?>


 


 
  <div class="col-md-12 mb-4">
    <div class="card border-info text-center">
      <div class="card-body">
        <h5 class="card-title">🗺️ Mapa odwiedzin</h5>
        <div id="map" style="height: 400px; border-radius: 6px;"></div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-success">
      <div class="card-body">
        <h5 class="card-title">🌍 Najczęściej odwiedzające kraje</h5>
        <ul class="list-group">
          <?php foreach ($topCountries as $c): ?>
            <li class="list-group-item d-flex justify-content-between">
              <?= htmlspecialchars($c['country']) ?>
              <span class="badge bg-success"><?= $c['total'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
   
  <div class="col-md-6">
    <div class="card border-secondary">
      <div class="card-body">
        <h5 class="card-title">📌 Ostatnie odwiedziny</h5>
        <ul class="list-group">
          <?php foreach ($lastIps as $v): ?>
            <li class="list-group-item">
              <strong><?= $v['ip'] ?></strong> – <?= htmlspecialchars($v['country']) ?>, <?= htmlspecialchars($v['city']) ?><br>
              <small><?= $v['visited_at'] ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
 

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  const map = L.map('map').setView([20, 0], 2);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);
  const points = <?= json_encode($points) ?>;
  points.forEach(p => {
    if (p.lat && p.lon) {
      L.marker([p.lat, p.lon])
        .addTo(map)
        .bindPopup(`<strong>${p.country}</strong><br>${p.city}<br><small>${p.ip}</small>`);
    }
  });
</script>
