<?php
// Diagnostic local uniquement — ne pas exposer en production

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Forbidden');
}

require_once __DIR__ . '/../includes/config.php';

if (GEMINI_API_KEY === '') {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("GEMINI_API_KEY absente : copiez .env.example vers .env et renseignez la clé.\n");
}

$api_key = trim(GEMINI_API_KEY);
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode($api_key);
$opts = [
    'http' => [
        'ignore_errors' => true,
        'header' => "Content-Type: application/json\r\n",
        'timeout' => 15,
    ],
];
$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);
$data = json_decode($response, true);
header('Content-Type: text/plain; charset=UTF-8');
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        echo $model['name'] . "\n";
    }
} else {
    echo "NO MODELS FOUND. ERROR: " . $response . "\n";
}
