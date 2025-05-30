<?php

function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function formatDate($datetime) {
    return date('d-m-Y', strtotime($datetime));
}

function generateClientNumber($lastId = 0) {
    $now = date("dmy"); // np. 180524
    return str_pad($lastId + 1, 3, '0', STR_PAD_LEFT) . '/' . $now;
}


function formatDateTime($dt) {
    return date("d.m.Y H:i", strtotime($dt));
}




/*
function generateShortHash($input, $length = 10) {
    $hash = hash('sha256', $input, true);
    $short = substr($hash, 0, 8);
    $base62 = base62Encode($short);
    return substr($base62, 0, $length);
}

function base62Encode($data) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num = gmp_import($data);
    $result = '';
    while (gmp_cmp($num, 0) > 0) {
        $index = gmp_intval(gmp_mod($num, 62));
        $result .= $chars[$index];
        $num = gmp_div_q($num, 62);
    }
    return strrev($result);
}

*/









 

 
