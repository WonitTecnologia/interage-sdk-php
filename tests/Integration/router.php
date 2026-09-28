<?php

// Servidor de teste (php -S): devolve, no envelope da API, o que recebeu.
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/api/public/limite') {
    http_response_code(429);
    header('Retry-After: 7');
    header('Content-Type: application/json');
    echo json_encode(['code' => 429, 'status' => 'TOO_MANY_REQUESTS', 'message' => 'Limite excedido']);
    return;
}

$files = [];
foreach ($_FILES as $field => $file) {
    $files[$field] = ['name' => $file['name'], 'content' => file_get_contents($file['tmp_name'])];
}

header('Content-Type: application/json');
echo json_encode(['code' => 200, 'status' => 'OK', 'message' => 'ok', 'data' => [
    'method' => $_SERVER['REQUEST_METHOD'],
    'path' => $path,
    'query' => $_GET,
    'authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'post' => $_POST,
    'files' => $files,
    'json' => json_decode((string) file_get_contents('php://input'), true),
]]);
