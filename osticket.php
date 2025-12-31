<?php

require_once 'config.php';
require_once 'logger.php';

// POST
function create_ticket($data)
{
    $keys = [
        '192.168.1.86' => env('OSTICKET_API_KEY_PI_4'),
        '192.168.1.134' => env('OSTICKET_API_KEY_PI_5'),
    ];

    $ip = $_SERVER['SERVER_ADDR'];
    $x_api_key = $keys[$ip] ?? null;

    if (!$x_api_key) {
        http_response_code(403);
        exit('Unauthorized IP' . $ip);
    }

    $url = env('OSTICKET_API_URI').'.json';

    // Basic validation
    $requiredFields = ['name', 'email', 'subject', 'message'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required field: $field"]);
            exit;
        }
    }

    // Build payload for osTicket
    $payload = [
        "alert" => true,
        "autorespond" => true,
        "source" => "API",

        "name" => trim($data['name']),
        "email" => trim($data['email']),
        "subject" => trim($data['subject']),
        "message" => trim($data['message']),

        // Optional fields
        "phone" => isset($data['phone']) ? trim($data['phone']) : null,
        "ip" => $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    // Remove null values (osTicket prefers clean payloads)
    $payload = array_filter($payload, fn($v) => $v !== null);

    // Attachments (optional)
    if (isset($data['attachments']) && is_array($data['attachments'])) {
        $payload['attachments'] = [];

        foreach ($data['attachments'] as $attachment) {
            if (is_array($attachment)) {
                foreach ($attachment as $filename => $fileData) {
                    // Basic safety check
                    if (is_string($filename) && is_string($fileData)) {
                        $payload['attachments'][] = [
                            $filename => $fileData
                        ];
                    }
                }
            }
        }
    }

    // Send to osTicket
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'X-API-Key: ' . $x_api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);
    //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        http_response_code(500);
        echo json_encode([
            "message" => "cURL error",
            "error" => curl_error($ch)
        ]);
        exit;
    }

    curl_close($ch);

    // Return osTicket response directly
    http_response_code($httpCode);
    echo $response;
}

//GET all

function get_tickets()
{
    $keys = [
        '192.168.1.86' => env('OSTICKET_API_KEY_PI_4'),
        '192.168.1.134' => env('OSTICKET_API_KEY_PI_5'),
    ];

    $ip = $_SERVER['SERVER_ADDR'];
    $x_api_key = $keys[$ip] ?? null;

    if (!$x_api_key) {
        http_response_code(403);
        exit('Unauthorized IP' . $ip);
    }

    $url = env('OSTICKET_API_URI').'.json';
    // Send to osTicket
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'X-API-Key: ' . $x_api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        http_response_code(500);
        echo json_encode([
            "message" => "cURL error",
            "error" => curl_error($ch)
        ]);
        exit;
    }

    curl_close($ch);

    // Return osTicket response directly
    http_response_code($httpCode);
    echo $response;
}

// GET one

function get_ticket($tik)
{
    $keys = [
        '192.168.1.86' => env('OSTICKET_API_KEY_PI_4'),
        '192.168.1.134' => env('OSTICKET_API_KEY_PI_5'),
    ];

    $ip = $_SERVER['SERVER_ADDR'];
    $x_api_key = $keys[$ip] ?? null;

    if (!$x_api_key) {
        http_response_code(403);
        exit('Unauthorized IP' . $ip);
    }

    $url = env('OSTICKET_API_URI').'/'.$tik.'.json';
    
    // Send to osTicket
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'X-API-Key: ' . $x_api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        http_response_code(500);
        echo json_encode([
            "message" => "cURL error",
            "error" => curl_error($ch)
        ]);
        exit;
    }

    curl_close($ch);

    // Return osTicket response directly
    http_response_code($httpCode);
    echo $response;
}