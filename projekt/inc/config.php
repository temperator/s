<?php
$host = '192.168.101.81';
$db   = 'dfde_DE4891lo';
$user = 'dfde_DE4891lo';
$pass = '[%#[F$ADYZc(I~R!o=';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("B³¹d po³¹czenia z baz¹: " . $e->getMessage());
}

 
