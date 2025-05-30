<?php
if (!isset($_SESSION)) session_start();
require_once "inc/config.php";

// Nie śledź adminów
if (isset($_SESSION['admin'])) return;

$ip = $_SERVER['REMOTE_ADDR'];

// Czy już odwiedzał dziś?
$stmt = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE ip = ? AND DATE(visited_at) = CURDATE()");
$stmt->execute([$ip]);
if ($stmt->fetchColumn() > 0) return;

// Ustal dane o lokalizacji IP
$json = file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city,lat,lon");
$data = json_decode($json, true);

$country = $data['country'] ?? null;
$city = $data['city'] ?? null;
$lat = $data['lat'] ?? null;
$lon = $data['lon'] ?? null;

// Zapisz do bazy
$stmt = $pdo->prepare("INSERT INTO visitors (ip, country, city, lat, lon) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$ip, $country, $city, $lat, $lon]);
?>
