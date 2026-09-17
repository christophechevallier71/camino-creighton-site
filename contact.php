<?php
// Simple contact-form mailer. Works out of the box on Hostinger shared hosting (PHP + mail()).
$to = "pilar.fertilitycare@gmail.com";

function clean($v) {
  return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: contact.html');
  exit;
}

$prenom = clean($_POST['prenom'] ?? '');
$nom = clean($_POST['nom'] ?? '');
$email = clean($_POST['email'] ?? '');
$situation = clean($_POST['situation'] ?? '');
$modalite = clean($_POST['modalite'] ?? '');
$message = clean($_POST['message'] ?? '');

if ($prenom === '' || $nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header('Location: contact.html?error=1');
  exit;
}

$subject = "Nouvelle demande de contact — Camino Creighton";
$body = "Prénom : $prenom\n"
      . "Nom : $nom\n"
      . "Email : $email\n"
      . "Situation : $situation\n"
      . "Format préféré : $modalite\n"
      . "Message : $message\n";

$headers = "From: Camino Creighton <no-reply@caminocreighton.com>\r\n"
         . "Reply-To: $email\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n";

mail($to, $subject, $body, $headers);

header('Location: merci.html');
exit;
