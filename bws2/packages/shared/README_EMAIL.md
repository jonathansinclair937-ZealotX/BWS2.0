# Email setup (PHPMailer)

1) Install dependencies:
```
composer install
```

2) Configure SMTP in `.env`:
```
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=apikey_or_user
SMTP_PASS=secret
SMTP_FROM="BWS Notifications <no-reply@yourdomain.com>"
SMTP_SECURE=tls
```

3) Run notifications sender:
```
php bws2/scripts/notify.php
```

If PHPMailer isn't installed, we fallback to PHP `mail()` automatically.
