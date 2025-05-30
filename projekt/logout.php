<?php
session_start();

// Usuwamy tylko dane usera (admin zostaje nietkniêty)
unset($_SESSION['user']);

// Opcjonalnie: mo¿na zniszczyæ ca³¹ sesjê, ale wtedy usuwa te¿ np. koszyk (jeœli wspólny)
session_write_close();

header("Location: index.php");
exit;
