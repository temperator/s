<?php
session_start();
require_once "inc/config.php";
require_once "inc/security_user.php"; // ważne: plik dla użytkownika (nie admina!)
requireLogin();

$user_id = $_SESSION['user']['id'];

// Pobierz wspomnienia + data płatności
$stmt = $pdo->prepare("
    SELECT m.id, m.memory_number, m.type, m.status, m.is_paid, m.blocked, m.created_at,
           p.paid_at
    FROM memories m
    LEFT JOIN payments p ON p.memory_id = m.id AND p.status = 'paid'
    WHERE m.user_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id]);
$memories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje wspomnienia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="container my-4">
    <h2>🕊️ Moje wspomnienia</h2>

    <?php if (!$memories): ?>
        <div class="alert alert-warning">Brak wspomnień.</div>
    <?php else: ?>
        <table class="table table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nr wspomnienia</th>
                    <th>Typ</th>
                    <th>Status</th>
                    <th>Opłacone</th>
                    <th>Aktywne do</th>
                    <th>Opcje</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($memories as $m): ?>
                <tr>
                    <td><?= ucfirst($m['id']) ?>/<?= htmlspecialchars($m['memory_number']) ?></td>
                    <td><?= $m['type'] === 'family' ? '👪 Rodzinne' : '👤 Pojedyncze' ?></td>
                    <td><?= ucfirst($m['status']) ?></td>
                    <td><?= $m['is_paid'] ? '✅ Tak' : '❌ Nie' ?></td>
                    <td>
                        <?php
                        if ($m['paid_at']) {
                            $expire = date('d.m.Y', strtotime($m['paid_at'] . ' +1835 days'));
                            echo $expire;
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
					
					 

					
					
					
					
                       <td>
					   
			<form method="POST" action="delete_my_memory.php" style="display:inline;" onsubmit="return confirm('Na pewno usunąć?')">
        <input type="hidden" name="id" value="<?= $m['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger">🗑️ Usuń</button>
    </form>		   
					   
    <a href="preview_memory.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">🔍 Podgląd</a>
    <?php if (!$m['is_paid']): ?>
        <a href="pay_memory.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-success">💳 Opłać</a>
    <?php endif; ?>

    
</td>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">✅ Wspomnienie zostało usunięte.</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>
