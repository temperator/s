<?php
require_once "inc/config.php";

// Pobierz IP
function getUserIP() {
    return $_SERVER['HTTP_CLIENT_IP'] ?? 
           $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
           $_SERVER['REMOTE_ADDR'];
}

$ip = getUserIP();

// Sprawdź czy IP już dzisiaj było zapisane
$stmt = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE ip = ? AND DATE(visited_at) = CURDATE()");
$stmt->execute([$ip]);
if ($stmt->fetchColumn() == 0) {

    // Pobierz dane geolokalizacyjne
    $geo = json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country,city,lat,lon"), true);

    if ($geo['status'] === 'success') {
        $stmt = $pdo->prepare("INSERT INTO visitors (ip, country, city, lat, lon) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ip, $geo['country'], $geo['city'], $geo['lat'], $geo['lon']]);
    }
}
?>
