<?php
require 'vendor/autoload.php';
require_once 'config.php';
require_once 'logger.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

function post_tweet($message) {
    $url = 'https://api.twitter.com/2/tweets';

    $oauth = [
        'oauth_consumer_key'     => env('TWITTER_API_KEY'),
        'oauth_token'            => env('TWITTER_ACCESS_TOKEN'),
        'oauth_nonce'            => bin2hex(random_bytes(16)),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp'        => time(),
        'oauth_version'          => '1.0',
    ];

    $params = ['text' => $message];

    // Prepare base string
    $base_info = build_base_string($url, 'POST', $oauth);
    $composite_key = rawurlencode(env('TWITTER_API_SECRET')) . '&' . rawurlencode(env('TWITTER_ACCESS_SECRET'));
    $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));

    $auth_header = 'OAuth ' . build_authorization_header($oauth);

    $client = new Client();

    try {
        $res = $client->post($url, [
            'headers' => [
                'Authorization' => $auth_header,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode($params),
        ]);

        $data = json_decode($res->getBody(), true);
        log_event("Tweet sent: " . $message);

        return ['success' => true, 'tweet_id' => $data['data']['id']];
    } catch (RequestException $e) {
        $error = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        log_event("Twitter error: $error", 'error');

        return ['success' => false, 'status' => $e->getCode(), 'error' => $error];
    }
}

function build_base_string($baseURI, $method, $params) {
    ksort($params);
    $r = [];
    foreach ($params as $key => $value) {
        $r[] = rawurlencode($key) . '=' . rawurlencode($value);
    }
    return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}

function build_authorization_header($oauth) {
    $r = [];
    foreach ($oauth as $key => $value) {
        $r[] = "$key=\"" . rawurlencode($value) . "\"";
    }
    return implode(', ', $r);
}