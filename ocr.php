<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
set_time_limit(60);
ini_set('default_socket_timeout', 60);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$apiKey = 'K82274717088957';

$data = json_decode(file_get_contents("php://input"), true);
$base64 = $data['image'] ?? '';

if (!$base64) {
    echo json_encode(['success' => false, 'error' => 'No image provided']);
    exit;
}

$payload = [
    'base64Image' => $base64,  // ✅ Just raw base64, no prefix
    'language' => 'eng',
    'isOverlayRequired' => 'false',
    'OCREngine' => 1,
    'detectOrientation' => 'true',
    'scale' => 'true',
    'isCreateSearchablePdf' => 'false'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Optional: Log everything
file_put_contents("ocr_debug.txt", "Payload:\n" . print_r($payload, true) . "\nHTTP CODE: {$info['http_code']}\nERROR: $err\nRESPONSE:\n$response");

if ($err || !$response) {
    echo json_encode([
        'success' => false,
        'error' => $err ?: 'Empty response from OCR API'
    ]);
} else {
    echo $response;
}
