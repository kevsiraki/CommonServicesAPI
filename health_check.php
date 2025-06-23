<?php
function health_check() {
    return [
        'status' => 'ok',
        'node_ip' => $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'],
        'node_hostname' => gethostname(),
        'timestamp' => time()
    ];
}
