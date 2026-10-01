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

// The mail is plain text, so values must NOT be HTML-escaped (that turned
// apostrophes, quotes and "&" into "&#039;", "&quot;", "&amp;" in the inbox).
// Only control characters are stripped: newlines in names/email would allow
// header injection, and in free text they are normalised to "\n".
function clean($v) {
  $v = trim((string)($v ?? ''));
  $v = preg_replace('/\r\n?/', "\n", $v);
  return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
}

function clean_line($v) {
  return trim(preg_replace('/\s+/u', ' ', clean($v)));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: contact.html');
  exit;
}

$type = clean_line($_POST['type'] ?? 'reservation');
$prenom = clean_line($_POST['prenom'] ?? '');
$nom = clean_line($_POST['nom'] ?? '');
$email = clean_line($_POST['email'] ?? '');

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
  $formule = clean_line($_POST['formule'] ?? '');
  $modalite = clean_line($_POST['modalite'] ?? '');
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
    $mail->Encoding = 'base64'; // accents survive any relay, no 8-bit surprises

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
  $sent = mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, $headers);
}

header('Location: ' . ($sent ? 'merci.html' : 'contact.html?error=1'));
exit;
