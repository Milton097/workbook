<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ocr.php

$apiKey = 'K82274717088957'; 

$data = json_decode(file_get_contents("php://input"), true);
$base64 = $data['image'] ?? '';

if (!$base64) {
    echo json_encode(['success' => false, 'error' => 'No image provided']);
    exit;
}
// if (isset($_FILES['file'])) {
//     $base64 = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
//     $base64 = "data:image/png;base64," . $base64;
// }

$payload = [
    'base64Image' => "data:image/png;base64," . $base64,
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


$response = curl_exec($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Log for debugging
file_put_contents("ocr_debug.txt", "HTTP CODE: {$info['http_code']}\nERROR: $err\nRESPONSE:\n$response");

// Check and return
if ($err || !$response) {
    echo json_encode([
        'success' => false,
        'error' => $err ?: 'Empty response from OCR API'
    ]);
} else {
    echo $response;
}
