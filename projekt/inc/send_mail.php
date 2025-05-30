<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . 'libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . 'libs/PHPMailer/SMTP.php';
require_once __DIR__ . 'libs/PHPMailer/Exception.php';

function sendWelcomeEmail($to, $name) {
    $mail = new PHPMailer(true);

    try {
        // Konfiguracja SMTP  
        $mail->isSMTP();  
        $mail->Host       = 'wn31.webd.pl'; // lub smtp.twojadomena.pl
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@digitaler-friedhof.de'; // pełny adres
        $mail->Password   = 'rakowa10'; // wygeneruj w ustawieniach Google!
        $mail->SMTPSecure = 'tls'; // lub 'ssl'
        $mail->Port       = 587; // lub 465 dla ssl

        $mail->setFrom('no-reply@digitaler-friedhof.de', 'Digital Cemetery');
        $mail->addAddress($to, $name);
   
        $mail->isHTML(true);
        $mail->Subject = 'Witaj w Digital Cemetery';
        $mail->Body    = "<h3>Cześć $name!</h3>
                          <p>Dziękujemy za rejestrację. Twoje konto jest już aktywne.</p>
                          <p><a href='https://dfde.webd.pro/login.php'>Zaloguj się teraz</a></p>
                          <p>Pozdrawiamy,<br>Zespół Digital Cemetery</p>
 
          <p>   ⚠️ Proszę nie odpowiadać na tę wiadomość — została wygenerowana automatycznie.</p>";
 

        $mail->AltBody = "Cześć $name,\nDziękujemy za rejestrację.\nZaloguj się: https://dfde.webd.pro/login.php";

        $mail->send();  
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}
