<?php
session_start();

// Usuwamy tylko dane usera (admin zostaje nietkni�ty)
unset($_SESSION['user']);

// Opcjonalnie: mo�na zniszczy� ca�� sesj�, ale wtedy usuwa te� np. koszyk (je�li wsp�lny)
session_write_close();

header("Location: index.php");
exit;
