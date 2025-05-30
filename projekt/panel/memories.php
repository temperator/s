<?php
require_once "inc/config.php";
require_once "inc/security.php";
requireAdmin();







$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$params = [];
$where = '';

if ($search !== '') {
  $where = "WHERE u.first_name LIKE :s OR u.last_name LIKE :s OR u.client_number LIKE :s OR m.memory_number LIKE :s OR m.status LIKE :s";
  $params[':s'] = '%' . $search . '%';
}

// liczba rekordów
$countSql = "SELECT COUNT(*) FROM memories m JOIN users u ON u.id = m.user_id $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// dane
$sql = "SELECT m.id, m.memory_number, m.type, m.status, m.is_paid, m.blocked,
               u.client_number, u.first_name, u.last_name
        FROM memories m
        JOIN users u ON u.id = m.user_id
        $where
        ORDER BY m.created_at DESC
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
  $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
  $stmt->bindValue($k, $v, $type);
}
$stmt->execute();
$memories = $stmt->fetchAll(PDO::FETCH_ASSOC);

 
?>

<?php include "header.php"; ?>

<style>
.status-dot {
    width: 14px;
    height: 14px;
    border-radius: 0%;
    display: inline-block;
    margin: auto;
  
}
.dot-green {
    background-color: #4CAF50; /* zielony */	
}
.dot-red {
    background-color: #f44336; /* czerwony */	
}
</style>




<div class="container mt-4">
  <h5>🧠 Lista wspomnień</h5>

 

  	 <form method="get" class="mb-4 d-flex justify-content-end">
        <input type="text" name="search" class="form-control w-100 me-2" value="<?= htmlspecialchars($search) ?>" placeholder="Szukaj">
        <button class="btn btn-primary">Szukaj</button>
    </form>
  
  
 
   


  
  
  
  <?php if (!$memories): ?>
    <div class='alert alert-warning'>❌ Brak wyników.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class='table table-striped align-middle text-center'>
      <thead class='table-dark'>
        <tr>
          <th>id</th>		
          <th>Nr klienta</th>
          <th>Nr wspomnienia</th>
          <th>Typ</th>
          <th>Płatność</th>  <th>Płatność</th>
          <th>Status</th>
          <th>Blokada</th>
          <th>Usuń</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($memories as $m): ?>
          <tr> 
		   <td><?= htmlspecialchars($m['id']) ?></td>
            <td><?= htmlspecialchars($m['client_number']) ?></td>
            <td><?= htmlspecialchars($m['memory_number']) ?></td>
            <td><?= $m['type'] === 'single' ? '👤' : '👪' ?></td>
            <td> 
			 
			
			<?= $m['is_paid'] ? '✔️' : '❌' ?>
			 
			
			 

			</td>
			
			 <td>
    <?php if (!$m['is_paid']): ?>
        <a href='activate_memory.php?id=<?= $m['id'] ?>' class='btn btn-sm btn-danger me-1'>Aktywuj</a>
    <?php else: ?>
        <button class='btn btn-sm btn-outline-success me-1' disabled>Aktywne</button>
		
    <?php endif; ?>
</td>

			
            <td><?= ucfirst($m['status']) ?></td>
 
            <td>
			
			
			 
			
			
			<?= $m['blocked'] ? '❌' : '✔️' ?>
			
			
			 	
			 <a href='toggle_block_memory.php?id=<?= $m['id'] ?>&block=<?= $m['blocked'] ? "0" : "1" ?>' class='btn btn-sm btn-outline-<?= $m['blocked'] ? "success" : "warning" ?> me-1'><?= $m['blocked'] ? "🔒" : "🔓" ?></a>
			</td>		
            <td>
             
			   

			   
			  
			  

              <a href='delete_memory.php?id=<?= $m['id'] ?>' class='btn btn-sm btn-danger' onclick="return confirm('❗ Na pewno usunąć?')">🗑️</a>
			  
			  
			  
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>
    <nav><ul class='pagination justify-content-center'>
      <?php
        if ($totalPages > 1) {
          if ($page > 1) echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "&search=$search'>‹‹‹</a></li>";
          else echo "<li class='page-item disabled'><span class='page-link'>‹‹‹</span></li>";

          $range = 2;
          for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || ($i >= $page - $range && $i <= $page + $range)) {
              $active = ($i == $page) ? 'active' : '';
              echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=$search'>$i</a></li>";
            } elseif ($i == 2 || $i == $totalPages - 1) {
              echo "<li class='page-item disabled'><span class='page-link'>…</span></li>";
            }
          }

          if ($page < $totalPages) echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "&search=$search'>›››</a></li>";
          else echo "<li class='page-item disabled'><span class='page-link'>›››</span></li>";
        }
      ?>
    </ul></nav>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
