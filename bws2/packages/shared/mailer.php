<?php
// packages/shared/mailer.php — PHPMailer if available; fallback to mail()
require_once __DIR__ . '/db.php';

function mailer_env($k,$d=''){ return env_get($k,$d); }

function bws_send_mail($to, $subject, $body) {
  // Try PHPMailer first
  if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    try {
      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = mailer_env('SMTP_HOST','localhost');
      $mail->Port = intval(mailer_env('SMTP_PORT','25'));
      $secure = mailer_env('SMTP_SECURE','');
      if ($secure) $mail->SMTPSecure = $secure; // 'tls' or 'ssl'
      $user = mailer_env('SMTP_USER','');
      $pass = mailer_env('SMTP_PASS','');
      if ($user || $pass) { $mail->SMTPAuth = true; $mail->Username = $user; $mail->Password = $pass; }
      $from = mailer_env('SMTP_FROM','no-reply@bws.local');
      // Parse "Name <email>"
      if (preg_match('/^(.+?)\s*<(.+?)>$/', $from, $m)) {
        $mail->setFrom($m[2], $m[1]);
      } else {
        $mail->setFrom($from);
      }
      $mail->addAddress($to);
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->AltBody = $body;
      $mail->send();
      return true;
    } catch (Throwable $e) {
      // fall through to mail()
    }
  }
  // Fallback
  $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
  $from = mailer_env('SMTP_FROM','no-reply@bws.local');
  $headers .= "From: $from\r\n";
  @mail($to, $subject, $body, $headers);
  return true;
}
