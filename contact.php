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

$headers = "From: Camino Creighton <no-reply@caminocreighton.com>\r\n"
         . "Reply-To: $email\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n";

mail($to, $subject, $body, $headers);

header('Location: merci.html');
exit;
