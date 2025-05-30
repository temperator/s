<?php
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}



function countryToFlag($code) {
    if (!$code || strlen($code) !== 2) return '';
    $code = strtoupper($code);
    return mb_chr(127397 + ord($code[0]), 'UTF-8') .
           mb_chr(127397 + ord($code[1]), 'UTF-8');
}


function getPendingOrdersCount(PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM memories WHERE status NOT IN ('zrealizowano')");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}



 
 