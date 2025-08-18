<?php
// Simple Token Service (Prototype)
// NOTE: Replace with secure JWT library in production

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $username = $body['username'] ?? '';
    $password = $body['password'] ?? '';
    // TODO: Verify against bws2_users table
    if ($username === 'demo' && $password === 'password') {
        $token = base64_encode(json_encode([
            'sub' => $username,
            'role' => 'Client',
            'iat' => time(),
            'exp' => time()+3600
        ]));
        echo json_encode([ 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => 3600 ]);
    } else {
        http_response_code(401);
        echo json_encode([ 'error' => 'invalid_credentials' ]);
    }
} elseif ($method === 'GET') {
    echo json_encode([ 'status' => 'Token Service running' ]);
} else {
    http_response_code(405);
}
?>