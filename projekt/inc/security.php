<?php
session_start();

function isAdmin() {
    return isset($_SESSION['admin']);
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}



