<?php
// Copy this file to "mail-config.php" (same folder) and fill in the real
// Hostinger mailbox credentials below.
//
// IMPORTANT: mail-config.php must be created directly on the server via
// Hostinger's File Manager (hPanel → Fichiers → Gestionnaire de fichiers),
// NOT through git. It's intentionally excluded from this repository (see
// .gitignore) so the password never ends up on GitHub, which is public.
return [
  'host' => 'smtp.hostinger.com',
  'port' => 465,
  'username' => 'no-reply@caminocreighton.com', // the mailbox you create in hPanel
  'password' => 'REMPLACE-MOI',
];
