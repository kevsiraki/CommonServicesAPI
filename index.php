<?php
header('Content-Type: application/json');

require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

require_once 'send_email.php';
require_once 'send_discord.php';
require_once 'tweet.php';
require_once 'health_check.php';
require_once 'nextcloud.php';
require_once 'generate_pdf.php';
require_once 'helpers.php';

$uri = rtrim($_SERVER['REQUEST_URI'], '/');
$method = $_SERVER['REQUEST_METHOD'];

exec("nohup sudo /home/toggle_gpio.sh > /dev/null 2>&1 &");

switch (true) {
    case preg_match('/\/email$/', $uri):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($data['key']) || $data['key'] !== env('MASTER_SECRET_KEY')) {
                http_response_code(403);
                die(json_encode(['error' => 'Unauthorized: Invalid or missing key']));
            }

            if (empty($data['to'])) {
                http_response_code(400);
                die(json_encode(['error' => '"to" field is required to send emails.']));
            }

            $attachments = [];

            if (!empty($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $item) {
                    $filename = null;
                    $content = false;

                    // Associative array with base64
                    if (is_array($item) && isset($item['base64'])) {
                        $b64 = $item['base64'];
                        $filename = $item['filename'] ?? ('attachment_' . uniqid());
                        if (preg_match('/^data:.*?;base64,(.*)$/', $b64, $matches)) {
                            $b64_data = $matches[1];
                        } else {
                            $b64_data = $b64;
                        }
                        $b64_data = str_replace(' ', '+', $b64_data);
                        $content = base64_decode($b64_data, true);
                    }

                    // Raw base64 string with optional data URI
                    elseif (is_string($item) && preg_match('/^data:(.*?);base64,(.*)$/', $item, $matches)) {
                        $mime = trim($matches[1]);
                        $content = base64_decode($matches[2], true);
                        $ext = get_extension_from_mime($mime);
                        $filename = 'attachment_' . uniqid() . '.' . $ext;
                    }

                    // Raw base64 without data URI
                    elseif (is_string($item) && base64_decode($item, true) !== false) {
                        $content = base64_decode($item, true);
                        $filename = 'attachment_' . uniqid() . '.bin'; // default unknown type
                    }


                    // Associative array with a URL and optional filename
                    elseif (is_array($item) && isset($item['url']) && filter_var($item['url'], FILTER_VALIDATE_URL)) {
                        $content = fetch_file_from_url($item['url']);
                        $filename = $item['filename'] ?? basename(parse_url($item['url'], PHP_URL_PATH));
                    }

                    // Plain URL string
                    elseif (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                        $content = fetch_file_from_url($item);
                        $filename = basename(parse_url($item, PHP_URL_PATH));
                    }

                    // Validate the attachments
                    if ($content !== false && $filename !== null) {
                        $attachments[] = [
                            'filename' => $filename,
                            'content' => $content
                        ];
                    }
                }
            }

            echo json_encode(send_email(
                $data['to'],
                $data['name'] ?? '',
                $data['subject'] ?? '',
                html_entity_decode($data['body'] ?? ''),
                $attachments,
                $data['cc'] ?? '' // Optional CC field
            ));
        } else {
            http_response_code(405);
            die(json_encode(['error' => 'FAIL: POST method is required for this endpoint.']));
        }
        break;

    case preg_match('/\/discord$/', $uri):
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit(json_encode(['error' => 'FAIL: POST method is required for this endpoint.']));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            exit(json_encode(['error' => 'Invalid JSON payload']));
        }

        if (empty($data['key']) || $data['key'] !== env('MASTER_SECRET_KEY')) {
            http_response_code(403);
            exit(json_encode(['error' => 'Unauthorized: Invalid or missing key']));
        }

        if (empty($data['url'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'FAIL: "url" field is required to send Discord webhook requests.']));
        }
        if (empty($data['content']) && empty($data['embeds'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'FAIL: either "content" or "embeds" must be provided']));
        }

        // everything looks good — send it
        $result = send_discord($data);
        echo json_encode($result);
        break;

    case preg_match('/\/tweet$/', $uri):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($data['key']) || $data['key'] !== env('MASTER_SECRET_KEY')) {
                http_response_code(403);
                die(json_encode(['error' => 'Unauthorized: Invalid or missing key']));
            }
            if (empty($data['message']))
                die('FAIL: "message" field is required to Tweet.');
            echo json_encode(post_tweet($data['message']));
        } else
            die(json_encode(['error' => 'FAIL: POST method is required for this endpoint.']));
        break;

    case ($uri == '/CommonServices'):
    case preg_match('/\/health$/', $uri):
        echo json_encode(health_check());
        break;

    case preg_match('/\/easter$/', $uri):
        echo json_encode(['easter' => date("M-d-Y", easter_date()), 'daysAfterMarch21stForEasterThisYear' => easter_days()]);
        break;

    case preg_match('#/nc/nest$#', $uri):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                exit(json_encode(['error' => 'Invalid JSON payload']));
            }

            if (empty($data['key']) || $data['key'] !== env('MASTER_SECRET_KEY')) {
                http_response_code(403);
                exit(json_encode(['error' => 'Unauthorized: Invalid or missing key']));
            }

            echo json_encode(nc_nest(
                $data['path'] ?? '',
            ));
        } else {
            http_response_code(405);
            die(json_encode(['error' => 'FAIL: POST method is required for this endpoint.']));
        }
        break;

    case preg_match('/\/eflorm$/', $uri):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                exit(json_encode(['error' => 'Invalid JSON payload']));
            }

            if (empty($data['key']) || $data['key'] !== env('MASTER_SECRET_KEY')) {
                http_response_code(403);
                exit(json_encode(['error' => 'Unauthorized: Invalid or missing key']));
            }

            echo json_encode(geneFlorm(
                $data ?? '',
            ));
        } else {
            http_response_code(405);
            die(json_encode(['error' => 'FAIL: POST method is required for this endpoint.']));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown endpoint']);
}