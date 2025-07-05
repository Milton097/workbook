<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

function loadEnv($path)
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, "'\""); // remove quotes if any
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

loadEnv(__DIR__ . '/.env');
$apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $postFields = [
        "file" => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
        "model" => $_POST['model'] ?? 'whisper-1',
        "response_format" => $_POST['response_format'] ?? 'json',
        "language" => $_POST['language'] ?? 'en',
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.openai.com/v1/audio/transcriptions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey"
        ],
        CURLOPT_POSTFIELDS => $postFields,
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Curl error',
            'details' => $curlError
        ]);
    } elseif ($httpCode !== 200) {
        http_response_code($httpCode);
        echo json_encode([
            'error' => 'OpenAI API error',
            'http_status' => $httpCode,
            'response' => json_decode($response, true)
        ]);
    } else {
        echo $response; // already JSON
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
