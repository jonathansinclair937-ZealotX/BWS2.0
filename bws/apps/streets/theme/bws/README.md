# BWS OSSN Theme

Swap Listingo directory HTML for PSI Rails and Cards. Include this theme in your OSSN install.

## OSSN → BWS SSO Bridge

1. After successful OSSN login, redirect users to:
   `/apps/streets/theme/bws/login-bridge.html?u=<USERNAME>&dest=/apps/streets/theme/bws/index.php`

   > In production, do **not** pass `u` on the query string. Instead, server-side include OSSN engine,
   > detect the logged-in user, and render `login-bridge.html` (or a PHP version) with the username embedded.

2. The bridge will call `/packages/auth/ossn-bridge.php` to mint a BWS token,
   store it in `localStorage` as `bws2_token`, set a cookie, and redirect back to Streets.

3. Admin and Web apps read `bws2_token` to display session and gate features.
