    <?php 
	
	
	$memories = $pdo->query("SELECT id FROM memories")->fetchAll(PDO::FETCH_ASSOC);
foreach ($memories as $mem) {
    $hash = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT IGNORE INTO qr_links (memory_id, secure_hash) VALUES (?, ?)");
    $stmt->execute([$mem['id'], $hash]);
}

	
	
	
	?>