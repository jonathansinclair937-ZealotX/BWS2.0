<?php
// Session Middleware Prototype
function verify_token($token) {
    // TODO: Replace with JWT decode/verify
    $data = json_decode(base64_decode($token), true);
    if (!$data) return false;
    if ($data['exp'] < time()) return false;
    return $data;
}

// Example usage in protected route
/*
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401); exit;
}
list($type, $token) = explode(' ', $headers['Authorization'], 2);
if ($type !== 'Bearer') { http_response_code(401); exit; }
$user = verify_token($token);
if (!$user) { http_response_code(401); exit; }
*/
?>