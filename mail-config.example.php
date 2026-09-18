<?php
// Copy this file to "mail-config.php" (same folder) and fill in the real
// credentials below.
//
// IMPORTANT: mail-config.php must be created directly on the server via
// Hostinger's File Manager (hPanel → Fichiers → Gestionnaire de fichiers),
// NOT through git. It's intentionally excluded from this repository (see
// .gitignore) so the password never ends up on GitHub, which is public.
//
// Using Gmail (pilar.fertilitycare@gmail.com) as the sender:
//   1. Enable 2-Step Verification on the Google account (required).
//   2. Create an App Password at https://myaccount.google.com/apppasswords
//   3. Use that 16-character App Password below — NOT the normal Gmail
//      login password, which Google no longer accepts for this.
return [
  'host' => 'smtp.gmail.com',
  'port' => 465,
  'username' => 'pilar.fertilitycare@gmail.com',
  'password' => 'REMPLACE-MOI', // the 16-character App Password, not the Gmail login password
];
