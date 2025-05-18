<?php

//TODO: Look into https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c //DONE
//Testing

require_once 'config.php';
require_once 'logger.php';

function send_discord(array $data): array {
    // Extract & whitelist only fields Discord cares about:
    $allowed = [
        'content', 'username', 'avatar_url', 'tts', 'file',
        'embeds', 'allowed_mentions', 'components', 'attachments'
    ];

    $payload = [];
    foreach ($allowed as $key) {
        if (isset($data[$key])) {
            $payload[$key] = $data[$key];
        }
    }

    // If you want to force an embed timestamp or color
    // you can still massage $payload['embeds'] here, e.g.:
    // foreach ($payload['embeds'] as &$e) {
    //     if (empty($e['timestamp'])) {
    //         $e['timestamp'] = date('c');
    //     }
    // }
    // unset($e);

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($data['url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    log_event("Discord webhook sent: " . ($payload['content'] ?? '[embed]'));

    return [
        'success'  => ($httpCode >= 200 && $httpCode < 300),
        'status'   => $httpCode,
        'response' => $response,
        'payload'  => $payload,  // echo back what was sent, for debugging
    ];
}
