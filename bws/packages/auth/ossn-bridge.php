<?php
// OSSN → BWS SSO Bridge (Prototype)
// Goal: When a user logs into OSSN, mint a BWS token and pass it to the front-end.
//
// Integration notes:
// - In a real OSSN environment, include OSSN's engine and read the current logged-in user:
//     include_once('/path/to/ossn/engine/start.php');
//     $user = ossn_loggedin_user();
// - Here we accept a username via GET for scaffold/demo purposes.

header('Content-Type: application/json');

$username = $_GET['u'] ?? null;
$role = $_GET['role'] ?? 'Client';

if (!$username) {
  // TODO: replace with OSSN session user
  http_response_code(400);
  echo json_encode(['error'=>'missing_user','hint'=>'Provide ?u=USERNAME or integrate with OSSN session.']);
  exit;
}

// Prototype token (replace with JWT & secret signing)
$token = base64_encode(json_encode([
  'sub' => $username,
  'role' => $role,
  'iat' => time(),
  'exp' => time()+3600
]));

echo json_encode(['access_token'=>$token,'token_type'=>'bearer','expires_in'=>3600]);
