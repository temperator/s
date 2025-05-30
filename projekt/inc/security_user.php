<?php
if (!isset($_SESSION)) session_start();

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}


 