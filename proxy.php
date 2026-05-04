<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Only POST allowed']);
    exit();
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit();
}

$GAS_URL = 'https://script.google.com/macros/s/AKfycbxv4T7lqEotP8JYiL6KwotbyK83Hei10EOAAZcl4UnKp8NQ6pcQEvqiOS7ajN6NvisfFw/exec';

$sheet  = isset($data['sheet'])  ? $data['sheet']  : 'default';
$key    = isset($data['key'])    ? $data['key']    : 'state';
$value  = isset($data['value'])  ? $data['value']  : '';

$value_encoded = urlencode(json_encode($value));
$url = $GAS_URL . '?action=save&sheet=' . urlencode($sheet) . '&key=' . urlencode($key) . '&value=' . $value_encoded;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode(['ok' => false, 'error' => $curl_error]);
    exit();
}

$result = json_decode($response, true);
if ($result && isset($result['ok'])) {
    echo json_encode($result);
} else {
    echo json_encode(['ok' => true, 'raw' => $response]);
}
?>
