<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/security.php";
requireAdmin();

$view = $_GET['view'] ?? 'inbox';

// Pobierz wiadomości
if ($view === 'sent') {
    $messages = $pdo->query("SELECT m.*, u.email FROM messages m LEFT JOIN users u ON m.receiver_id = u.id WHERE is_admin = 1 ORDER BY sent_at DESC")->fetchAll();
} else {
    $messages = $pdo->query("SELECT m.*, u.email FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE is_admin = 0 ORDER BY sent_at DESC")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>📬 Wiadomości</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "header.php"; ?>  

<div class="container my-4">
    <h3>📬 Wiadomości <?= $view === 'sent' ? '(Wysłane)' : '(Odebrane)' ?></h3>
    <div class="mb-3">
        <a href="?view=inbox" class="btn btn-outline-primary btn-sm">📥 Odebrane</a>
        <a href="?view=sent" class="btn btn-outline-secondary btn-sm">📤 Wysłane</a>
    </div>

    <?php if (!$messages): ?>
        <p class="text-muted">Brak wiadomości.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📅 Data</th>
                    <th>📨 Temat</th>
                    <th>👤 <?= $view === 'sent' ? 'Do' : 'Od' ?></th>
                    <th>📝 Treść</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><?= date("d.m.Y H:i", strtotime($msg['sent_at'])) ?></td>
                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                        <td><?= htmlspecialchars($msg['email'] ?? 'admin') ?></td>
                        <td><?= nl2br(htmlspecialchars($msg['body'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
</body>
</html>