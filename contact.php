<?php
// Contact-form mailer, sent via Hostinger's authenticated SMTP relay.
//
// PHP's mail() function is not used here: it was silently dropping every
// message, because caminocreighton.com has no SPF record authorizing
// Hostinger's servers to send mail "From" that domain — to Gmail (and
// most providers) an unauthenticated From-domain looks like spoofing, so
// the message never arrives, with no error on either end.
require __DIR__ . '/lib/phpmailer/Exception.php';
require __DIR__ . '/lib/phpmailer/PHPMailer.php';
require __DIR__ . '/lib/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$to = "pilar.fertilitycare@gmail.com";

function clean($v) {
  return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: contact.html');
  exit;
}

$type = clean($_POST['type'] ?? 'reservation');
$prenom = clean($_POST['prenom'] ?? '');
$nom = clean($_POST['nom'] ?? '');
$email = clean($_POST['email'] ?? '');

if ($prenom === '' || $nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header('Location: contact.html?error=1');
  exit;
}

if ($type === 'question') {
  $message = clean($_POST['message'] ?? '');
  $subject = "Nouvelle question — Camino Creighton";
  $body = "Prénom : $prenom\n"
        . "Nom : $nom\n"
        . "Email : $email\n"
        . "Question : $message\n";
} else {
  $formule = clean($_POST['formule'] ?? '');
  $modalite = clean($_POST['modalite'] ?? '');
  $dispo = clean($_POST['dispo'] ?? '');
  $subject = "Nouvelle demande de réservation — Camino Creighton";
  $body = "Prénom : $prenom\n"
        . "Nom : $nom\n"
        . "Email : $email\n"
        . "Formule souhaitée : $formule\n"
        . "Format préféré : $modalite\n"
        . "Disponibilités : $dispo\n";
}

$configFile = __DIR__ . '/mail-config.php';
$sent = false;

if (file_exists($configFile)) {
  $config = require $configFile;
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['username'], 'Camino Creighton');
    $mail->addAddress($to);
    $mail->addReplyTo($email, "$prenom $nom");

    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    $sent = true;
  } catch (Exception $e) {
    error_log('Contact form mail error: ' . $mail->ErrorInfo);
  }
} else {
  // mail-config.php not created yet on the server — fall back to mail()
  // so the form still attempts something, though delivery isn't guaranteed.
  $headers = "From: Camino Creighton <no-reply@caminocreighton.com>\r\n"
           . "Reply-To: $email\r\n"
           . "Content-Type: text/plain; charset=UTF-8\r\n";
  $sent = mail($to, $subject, $body, $headers);
}

header('Location: ' . ($sent ? 'merci.html' : 'contact.html?error=1'));
exit;
