<?php
// "Buzón de San José": receives a letter (text and/or one attached document) and
// forwards it to Pilar with the subject "Carta San José".
// Same transport as contact.php: Hostinger's authenticated SMTP, configured in
// mail-config.php (created on the server, never committed).
require __DIR__ . '/lib/phpmailer/Exception.php';
require __DIR__ . '/lib/phpmailer/PHPMailer.php';
require __DIR__ . '/lib/phpmailer/SMTP.php';
require __DIR__ . '/lib/buzon-functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$to = 'pilar.fertilitycare@gmail.com';

function buzon_back(string $code): void {
  header('Location: cotignac.html?carta=' . $code . '#carta');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: cotignac.html#carta');
  exit;
}

// When the upload exceeds post_max_size PHP drops $_POST and $_FILES entirely.
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
  buzon_back('toobig');
}

$result = buzon_validate($_POST, $_FILES);
if ($result['code'] === 'spam') {
  // pretend it worked, send nothing
  buzon_back('ok');
}
if (!$result['ok']) {
  // 'invalid' (bad name/e-mail) is already blocked by the browser; show the generic error
  buzon_back($result['code'] === 'invalid' ? 'error' : $result['code']);
}

$configFile = __DIR__ . '/mail-config.php';
if (!file_exists($configFile)) {
  error_log('Buzon: mail-config.php missing, letter not sent');
  buzon_back('error');
}

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
  $mail->Encoding = 'base64';

  $mail->setFrom($config['username'], 'Camino Creighton');
  $mail->addAddress($to);
  buzon_build_mail($mail, $result['data']);

  $mail->send();
  buzon_back('ok');
} catch (Exception $e) {
  error_log('Buzon mail error: ' . $mail->ErrorInfo);
  buzon_back('error');
}
