<?php
function log_event($message, $type = 'info') {
    date_default_timezone_set('America/Los_Angeles');
    $entry = date('Y-m-d H:i:s') . " [$type] $message\n";
    file_put_contents(__DIR__ . '/logs/api.log', $entry, FILE_APPEND);
}
