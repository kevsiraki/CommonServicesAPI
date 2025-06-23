<?php
function log_event($message, $type = 'info') {
    date_default_timezone_set('America/Los_Angeles');
    
    $logDir = '/home/CSlog';
    $logFile = $logDir . '/api.log';
    
    // Check if the directory exists, if not create it
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0775, true)) {
            error_log("Failed to create log directory: $logDir");
            return;
        }
    }
    
    $entry = date('Y-m-d H:i:s') . " [$type] $message\n";
    
    if (file_put_contents($logFile, $entry, FILE_APPEND) === false) {
        error_log("Failed to write to log file: $logFile");
    }
}
