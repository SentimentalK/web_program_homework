<?php


function getJsonData() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonError('Invalid JSON', 400);
    }
    return $data;
}

function json_response($code, $message, $data = null) {
    header("Content-Type: application/json");
    $response = [
        'code' => $code,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

function check_required_fields($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field])) {
            json_response(400, "Missing $field");
        }
    }
}

function sendJsonError($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'code' => $code,
        'error' => $message
    ]);
    exit;
}