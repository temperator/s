<?php
require 'inc/libs/PHPMailer/PHPMailer.php';
require 'inc/libs/PHPMailer/SMTP.php';
require 'inc/libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);


try {
    $mail->isSMTP();
    $mail->Host       = 'wn31.webd.pl'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@digitaler-friedhof.de';
    $mail->Password   = 'rakowa10';
 
$mail->Port = 465;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;


$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';

    $mail->setFrom('no-reply@digitaler-friedhof.de', 'Digitaler Friedhof');
    $mail->addAddress('temperator@interia.pl');


	
	
    $mail->isHTML(true);
    $mail->Subject = 'Test wiadomości';
    $mail->Body    = 'To jest test.';
    $mail->send();

    echo "? E-mail został wysłany";
} catch (Exception $e) {
    echo "? Błąd: " . $mail->ErrorInfo;
}
?>
