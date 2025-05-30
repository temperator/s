<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security.php";
requireAdmin();

// ID wspomnienia
$id = intval($_GET['id'] ?? 0);
if (!$id) die("Brak ID zam�wienia.");

// ?? Najpierw upewnij si�, �e istnieje hash (je�li nie, generuj)
$check = $pdo->prepare("SELECT secure_hash FROM qr_links WHERE memory_id = ?");
$check->execute([$id]);
$existing_hash = $check->fetchColumn();

if (!$existing_hash) {
    $secure_hash = bin2hex(random_bytes(20));
    $pdo->prepare("INSERT INTO qr_links (memory_id, secure_hash) VALUES (?, ?)")->execute([$id, $secure_hash]);
}

// ?? Teraz pobieramy dane (ju� z QR!)
$stmt = $pdo->prepare("
    SELECT m.*, u.first_name, u.last_name, u.client_number, u.email,
           p.method, p.amount, p.status as payment_status, p.paid_at,
           s.*, q.secure_hash
    FROM memories m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN payments p ON p.memory_id = m.id
    LEFT JOIN shipping_data s ON s.user_id = u.id
    LEFT JOIN qr_links q ON q.memory_id = m.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) die("Nie znaleziono zam�wienia.");
 $qr_link = "https://dfde.webd.pro/" . $order['secure_hash'];

 //$qr_link = "https://dfde.webd.pro/" . $order['secure_hash'];
 
 
 
 
//$qr_link = "https://dfde.webd.pro/preview_memory_qr.php?hash=" . $order['secure_hash'];

//$qr_link = "http://localhost/200/preview_memory.php?hash=" . $order['secure_hash']; 



// QR GENERATOR (base64, nie zapisuje pliku)
//require_once "inc/phpqrcode/qrlib.php";
//ob_start();
//QRcode::png($qr_link, null, QR_ECLEVEL_L, 12);
//$qrBase64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());



require_once "inc/phpqrcode/qrlib.php";

$memorySafe = str_replace('/', '-', $order['memory_number']); // np. 048-280525-6
$qr_file_path = 'downloads/' . $memorySafe . '.png';






if (!file_exists($qr_file_path)) {
    QRcode::png($qr_link, $qr_file_path, QR_ECLEVEL_L, 12, 1);
}

$qrBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($qr_file_path));







// Zmiana statusu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_status'])) {
    $newStatus = $_POST['status'];
    $valid = ['oczekujący', 'w realizacji', 'zrealizowano', 'anulowano'];

    if (in_array($newStatus, $valid)) {
        $pdo->prepare("UPDATE memories SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        header("Location: view_order.php?id=$id&status=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>📄 Zamówienie <?= htmlspecialchars($order['memory_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .label-title { font-weight: bold; margin-bottom: .25rem; }
        .qr img { max-width: 120px; }
        .section { margin-bottom: 2rem; }
    </style>
</head>
<body>
<?php include "header.php"; ?>

<div class="container my-4">
    <h4 class="mb-4">📄 Szczegóły zamówienia: <strong><?= htmlspecialchars($order['memory_number']) ?></strong></h4>

    <?php if ($_GET['status'] ?? '' === 'updated'): ?>
        <div class="alert alert-success">✅ Status zamówienia zaktualizowany.</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 section">
            <div class="label-title">📦 Informacje</div>
            <p>Typ: <?= $order['type'] === 'single' ? '👤 Pojedyncze' : '👪 Rodzinne' ?></p>
            <p>Status: <strong class="<?= $order['status'] === 'zrealizowano' ? 'text-success' : 'text-danger' ?>">
                <?= ucfirst($order['status']) ?></strong></p>
            <p>Utworzone: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
            <?php if ($order['active_until']): ?>
                <p>Aktywne do: <strong><?= date('d.m.Y', strtotime($order['active_until'])) ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="col-md-6 section">
            <div class="label-title">📱 Kod QR</div>
            <div class="qr">
               <!-- <img src="< ?= $qrBase64 ?>" alt="QR" title="Zeskanuj QR">-->
				<img src="<?= $qr_file_path ?>" alt="QR" title="Zeskanuj QR">
				 <div class="mt-2">
    <a href="<?= $qr_file_path ?>" download class="btn btn-sm btn-outline-success">
        📥 Pobierz kod QR (<?= $memorySafe ?>.png)
    </a>
</div>


 






				
                <div class="mt-2">
                    <a href="<?= $qr_link ?>" target="_blank" class="btn btn-sm btn-outline-primary">🔗 Otwórz podgląd wspomnienia</a>
                </div>
            </div>
        </div>
		
		 



		
		

        <div class="col-md-6 section">
            <div class="label-title">💳 Płatność</div>
            <p>Metoda: <?= $order['method'] ?? '—' ?></p>
            <p>Kwota: <?= $order['amount'] ? number_format($order['amount'], 2, ',', ' ') . ' €' : '—' ?></p>
            <p>Opłacone: <?= $order['payment_status'] === 'completed' ? '✅ TAK' : '❌ NIE' ?></p>
            <p>Data: <?= $order['paid_at'] ? date('d.m.Y H:i', strtotime($order['paid_at'])) : '—' ?></p>
        </div>

        <div class="col-md-6 section">
            <div class="label-title">🏠 Adres wysyłki</div>
            <?php if ($order['street']): ?>
                <p><?= htmlspecialchars($order['first_name']) ?> <?= htmlspecialchars($order['last_name']) ?></p>
                <?php if ($order['company']) echo '<p>' . htmlspecialchars($order['company']) . '</p>'; ?>
                <p><?= htmlspecialchars($order['street']) ?>,
                   <?= htmlspecialchars($order['postal_code']) ?> <?= htmlspecialchars($order['city']) ?></p>
                <p>📞 <?= htmlspecialchars($order['phone']) ?> / ✉️ <?= htmlspecialchars($order['email']) ?></p>
                <?php if ($order['comment']) echo '<p><em>' . htmlspecialchars($order['comment']) . '</em></p>'; ?>
            <?php else: ?>
                <p class="text-danger">🚫 Brak danych adresowych.</p>
            <?php endif; ?>

            <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-2">🖨️ Etykieta</a>
            <a href="print_address.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark mt-2">PDF Adres</a>
        </div>

		
		
		
		
		
        <div class="col-md-6 section">
            <div class="label-title">👤 Klient</div>
            <p><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
            <p><?= htmlspecialchars($order['email']) ?></p>
            <p>Nr klienta: <strong><?= htmlspecialchars($order['client_number']) ?></strong></p>
        </div>

        <div class="col-md-6 section">
            <form method="post">
                <label for="status" class="form-label fw-bold">🛠️ Zmień status:</label>
                <select name="status" id="status" class="form-select w-auto d-inline-block">
                    <?php foreach (['oczekujący', 'w realizacji', 'zrealizowano', 'anulowano'] as $status): ?>
                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                            <?= ucfirst($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="save_status" class="btn btn-sm btn-primary ms-2">💾 Zapisz</button>
            </form>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="orders.php" class="btn btn-outline-secondary">← Powrót</a>
        <a href="print_order.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline-dark">🖨️ PDF</a>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>
