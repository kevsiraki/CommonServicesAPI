<?php
require_once 'config.php';
require_once 'logger.php';

function nc_nest($path)
{
    $root = env('NEXTCLOUD_ROOT');
    $folders = explode('/', $path);
    $currentPath = '';
    $responses = [];

    foreach ($folders as $folder) {
        if (empty($folder))
            continue;
        $currentPath .= $folder . '/';
        $url = $root . $currentPath;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, env('NEXTCLOUD_USER') . ':' . env('NEXTCLOUD_KEY'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responses[] = [
            'folder' => $currentPath,
            'status' => $httpCode,
            'response' => $result
        ];
    }

    // After building all responses...
    $logParts = [];
    foreach ($responses as $resp) {
        // Format: folder=/path, status=200, response=OK (or whatever the response is)
        $logParts[] = sprintf(
            "folder=%s, status=%s",
            $resp['folder'],
            $resp['status']
        );
    }

    log_event("Nextcloud nested folders creation status: " . implode(' | ', $logParts));

    return $responses;
}
