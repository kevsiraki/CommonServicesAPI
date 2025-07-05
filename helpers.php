<?php

/*
function fetch_file_from_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, ''.env('NEXTCLOUD_USER').':'.env('NEXTCLOUD_KEY').''); // handle auth
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $data = curl_exec($ch);
    if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return $data;
}
*/

/**
 * Downloads a file from a URL, optionally with Nextcloud credentials if matched.
 */
function fetch_file_from_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // If it's a Nextcloud URL, apply basic auth
    if (!empty($url) && isset($url) && !empty(env('NEXTCLOUD_BASE_URL')) && env('NEXTCLOUD_BASE_URL') !== NULL && strpos($url, env('NEXTCLOUD_BASE_URL')) === 0) {
        curl_setopt($ch, CURLOPT_USERPWD, env('NEXTCLOUD_USER') . ':' . env('NEXTCLOUD_KEY'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }

    $data = curl_exec($ch);
    if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $data;
}

// Mime extenion mapper
function get_extension_from_mime($mime) {
    $map = [
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'text/plain' => 'txt',
        'text/html' => 'html',
        'text/csv' => 'csv',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'video/mp4' => 'mp4',
    ];

    return $map[$mime] ?? 'bin';
}
