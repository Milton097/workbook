<?php
$config = parse_ini_file("config.ini", true);
$apiKey = $config['openai']['api_key'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.openai.com/v1/audio/transcriptions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey"
        ],
        CURLOPT_POSTFIELDS => [
            "file" => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
            "model" => $_POST['model'] ?? 'whisper-1',
            "response_format" => $_POST['response_format'] ?? 'json',
            "language" => $_POST['language'] ?? 'en',
        ]
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        http_response_code(500);
        echo json_encode(['error' => $error]);
    } else {
        echo $response;
    }
}
?>
