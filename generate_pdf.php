<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

function remove_emojis($text) {
    return preg_replace('/[\p{So}\p{Cn}\x{1F600}-\x{1F64F}'
                      . '\x{1F300}-\x{1F5FF}'
                      . '\x{1F680}-\x{1F6FF}'
                      . '\x{1F1E0}-\x{1F1FF}'
                      . '\x{2600}-\x{26FF}'
                      . '\x{2700}-\x{27BF}'
                      . '\x{1F900}-\x{1F9FF}'
                      . '\x{1FA70}-\x{1FAFF}'
                      . '\x{1F018}-\x{1F270}'
                      . '\x{238C}-\x{2454}'
                      . '\x{FE0F}]'
                      . '/u', '', $text);
}

function safe($str)
{
    return remove_emojis($str ?? '');
}

function geneFlorm($data)
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        exit();
    }

    $id = safe($data['id'] ?? '');
    $by = safe($data['by'] ?? '');
    $date = safe($data['date'] ?? '');
    $title = safe($data['title'] ?? '');
    $description = safe($data['description'] ?? '');
    $associates_str = safe($data['associates'] ?? '');
    $associates = array_filter(array_map('trim', explode(',', $associates_str)));

    $pdf = new \TCPDF('P', 'mm', 'A4');
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Comfortaa font (must exist in your TCPDF fonts directory or be added as TTF)
    $fontPath = __DIR__ . '/fonts/DejaVuSans.ttf';
    $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);
    $pdf->SetFont($fontname, '', 12);

    // Colors
    $color_title = [21, 21, 21];
    $color_accent = [59, 200, 140];
    $color_label = [150, 158, 151];
    $color_text = [80, 80, 80];
    $color_bg = [245, 245, 245];

    // Title
    $pdf->SetTextColorArray($color_title);
    $pdf->SetFontSize(20);
    $pdf->MultiCell(0, 10, $title, 0, 'C');
    $pdf->Ln(2);

    // Divider
    $pdf->SetDrawColorArray($color_accent);
    $pdf->SetLineWidth(0.8);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(5);

    // Meta info
    $pdf->SetFontSize(11);
    $pdf->SetTextColorArray($color_label);
    $pdf->Write(0, "By: ");
    $pdf->SetTextColorArray($color_text);
    $pdf->Write(0, $by . "    ");
    $pdf->SetTextColorArray($color_label);
    $pdf->Write(0, "Date: ");
    $pdf->SetTextColorArray($color_text);
    $pdf->Write(0, $date);
    $pdf->Ln(10);

    // Card
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->SetFillColorArray($color_bg);
    $pdf->SetDrawColorArray($color_accent);
    //$pdf->Rect($x, $y, 170, 100, 'DF');
    //$pdf->SetXY($x + 5, $y + 5);

    // Description
    $html = "<span style='color:rgb(80,80,80); font-size:11pt;'>"
          . nl2br($description)
          . "</span><br><br>";

    if (!empty($associates)) {
        $html .= "<span style='color:rgb(150,158,151); font-size:10pt;'>Associates:</span> ";
//        foreach ($associates as $assoc)
 {
            $html .= "<span style='background-color:rgb(124,204,116); color:#fff; padding:2px 6px; border-radius:4px; font-size:9pt;'>"
                  . implode(', ', $associates)
                  . "</span>";
        }
    }

    $pdf->writeHTMLCell(160, '', $x + 5, $y + 5, $html, 0, 1, false, true, 'L');

    // Save locally
    $localPath = sys_get_temp_dir() . "/{$id}_lore_report.pdf";
    $pdf->Output($localPath, 'F');

    // Upload to Nextcloud
    $nc_folder = "Lores/{$id}";
    nc_nest($nc_folder); 

    $remote_url = env('NEXTCLOUD_ROOT') . "{$nc_folder}/{$id}_lore_report.pdf";
    $ch = curl_init($remote_url);
    curl_setopt($ch, CURLOPT_USERPWD, env('NEXTCLOUD_USER') . ':' . env('NEXTCLOUD_KEY'));
    curl_setopt($ch, CURLOPT_PUT, true);
    $fp = fopen($localPath, 'r');
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localPath));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    unlink($localPath);

    // Stream file
    $ch = curl_init($remote_url);
    curl_setopt($ch, CURLOPT_USERPWD, env('NEXTCLOUD_USER') . ':' . env('NEXTCLOUD_KEY'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (in_array($httpCode, [200, 207]) && $fileContent !== false) {
        header('Content-Type: ' . ($contentType ?: 'application/pdf'));
        header('Content-Disposition: attachment; filename="' . $id . '_lore_report.pdf"');
        log_event("eFlorm generated for lore with ID: " . $id . ", and uploaded to: " . $remote_url);
        echo $fileContent;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Upload ok, download failed']);
    }
}
