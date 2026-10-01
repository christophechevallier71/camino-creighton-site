<?php
// Helpers for buzon.php ("Buzón de San José"): validation of the posted form and
// construction of the e-mail. Kept apart from buzon.php so they can be tested
// without sending anything.

const BUZON_MAX_BYTES = 8 * 1024 * 1024;
const BUZON_MAX_TEXT = 10000;

// extension => MIME types accepted for it (as detected by finfo, not as claimed by the browser)
function buzon_allowed_types(): array {
  return [
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword', 'application/vnd.ms-office', 'application/x-ole-storage', 'application/CDFV2', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
    'odt'  => ['application/vnd.oasis.opendocument.text', 'application/zip', 'application/octet-stream'],
    'rtf'  => ['text/rtf', 'application/rtf', 'text/plain'],
    'txt'  => ['text/plain'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
    'heic' => ['image/heic', 'image/heif', 'application/octet-stream'],
  ];
}

// Plain-text mail: never HTML-escape. Strip control characters; names/e-mail are single-line.
function buzon_clean(?string $v): string {
  $v = trim((string)$v);
  $v = preg_replace('/\r\n?/', "\n", $v);
  return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
}

function buzon_clean_line(?string $v): string {
  return trim(preg_replace('/\s+/u', ' ', buzon_clean($v)) ?? '');
}

function buzon_safe_filename(string $name): string {
  $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? 'carta';
  $name = trim($name, '._');
  return $name !== '' ? substr($name, -80) : 'carta';
}

// Returns ['ok' => bool, 'code' => string, 'data' => array].
// codes: ok | spam | empty | consent | file | toobig | invalid
function buzon_validate(array $post, array $files): array {
  // honeypot: bots fill every field, people never see this one
  if (trim((string)($post['website'] ?? '')) !== '') {
    return ['ok' => false, 'code' => 'spam', 'data' => []];
  }

  $nom = buzon_clean_line($post['nom'] ?? '');
  $email = buzon_clean_line($post['email'] ?? '');
  $texto = buzon_clean($post['carta'] ?? '');
  $lang = in_array(($post['lang'] ?? ''), ['es', 'fr'], true) ? $post['lang'] : '';

  if ($nom === '' || mb_strlen($nom) > 120) return ['ok' => false, 'code' => 'invalid', 'data' => []];
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'code' => 'invalid', 'data' => []];
  if (empty($post['consentement'])) return ['ok' => false, 'code' => 'consent', 'data' => []];
  if (mb_strlen($texto) > BUZON_MAX_TEXT) $texto = mb_substr($texto, 0, BUZON_MAX_TEXT);

  $file = null;
  $up = $files['fichier'] ?? null;
  if (is_array($up) && ($up['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $err = $up['error'];
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) return ['ok' => false, 'code' => 'toobig', 'data' => []];
    if ($err !== UPLOAD_ERR_OK) return ['ok' => false, 'code' => 'error', 'data' => []];
    if (($up['size'] ?? 0) > BUZON_MAX_BYTES) return ['ok' => false, 'code' => 'toobig', 'data' => []];

    $ext = strtolower(pathinfo((string)($up['name'] ?? ''), PATHINFO_EXTENSION));
    $allowed = buzon_allowed_types();
    if (!isset($allowed[$ext])) return ['ok' => false, 'code' => 'file', 'data' => []];

    // Check the real content type when the fileinfo extension is available (it is on
    // standard PHP builds). The file is only ever attached to a mail, never served.
    if (function_exists('finfo_open')) {
      $fi = finfo_open(FILEINFO_MIME_TYPE);
      $mime = (string)finfo_file($fi, $up['tmp_name']);
      finfo_close($fi);
      if (!in_array($mime, $allowed[$ext], true)) return ['ok' => false, 'code' => 'file', 'data' => []];
    }

    $file = ['path' => $up['tmp_name'], 'name' => buzon_safe_filename($up['name'])];
  }

  if ($texto === '' && $file === null) return ['ok' => false, 'code' => 'empty', 'data' => []];

  return ['ok' => true, 'code' => 'ok', 'data' => ['nom' => $nom, 'email' => $email, 'texto' => $texto, 'file' => $file, 'lang' => $lang]];
}

// Fills a PHPMailer instance (recipient/transport are set by the caller).
function buzon_build_mail($mail, array $d): void {
  $mail->Subject = 'Carta San José';
  $body = "Nombre : {$d['nom']}\n";
  $body .= 'Email : ' . ($d['email'] !== '' ? $d['email'] : '(no indicado)') . "\n";
  if ($d['lang'] !== '') $body .= 'Idioma de la página : ' . strtoupper($d['lang']) . "\n";
  $body .= 'Archivo adjunto : ' . ($d['file'] ? $d['file']['name'] : 'no') . "\n";
  $body .= "\n--- Carta ---\n" . ($d['texto'] !== '' ? $d['texto'] : '(sin texto: ver el archivo adjunto)') . "\n";
  $mail->Body = $body;
  if ($d['email'] !== '') $mail->addReplyTo($d['email'], $d['nom']);
  if ($d['file']) $mail->addAttachment($d['file']['path'], $d['file']['name']);
}
