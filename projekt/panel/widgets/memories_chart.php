<?php
$monthLabels = [];
$memoriesByMonth = [];

for ($i = 5; $i >= 0; $i--) {
    $label = date('M Y', strtotime("-$i month"));
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->execute([$month, $year]);
    $count = $stmt->fetchColumn();

    $monthLabels[] = $label;
    $memoriesByMonth[] = $count;
}
?>

 
       
               
                
       

 
 <div class="col-md-12 mb-4">
    <div class="card border-success text-center">
      <div class="card-body">
    <h5 class="card-title">📆 Liczba wspomnień – ostatnie 6 miesięcy</h5>
    <canvas id="memoriesChart" height="100"></canvas>
  </div>
</div>
</div>
<script>
  new Chart(document.getElementById('memoriesChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: <?= json_encode($monthLabels) ?>,
      datasets: [{
        label: 'Wspomnienia',
        data: <?= json_encode($memoriesByMonth) ?>,
        backgroundColor: 'rgba(66,165,245,0.2)',
        borderColor: '#42a5f5',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>


 


