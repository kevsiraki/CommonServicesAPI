<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require_once 'logger.php';

function send_email($to, $to_name, $subject, $html_body, $attachments = [], $cc = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = env('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('SMTP_USER');
        $mail->Password = env('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(env('SMTP_FROM_EMAIL'), env('SMTP_FROM_NAME'));

        // Handle multiple TO addresses with optional names
        $recipients = array_filter(array_map('trim', explode(';', $to)));
        foreach ($recipients as $recipient) {
            if (preg_match('/^(.*)<(.+)>$/', $recipient, $matches)) {
                $name = trim($matches[1], '" ');
                $email = trim($matches[2]);
            } else {
                $email = $recipient;
                $name = (count($recipients) === 1) ? $to_name : '';
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email, $name);
            }
        }

        // Handle optional CCs
        if (!empty($cc)) {
            $cc_list = array_filter(array_map('trim', explode(';', $cc)));
            foreach ($cc_list as $cc_recipient) {
                if (preg_match('/^(.*)<(.+)>$/', $cc_recipient, $matches)) {
                    $cc_name = trim($matches[1], '" ');
                    $cc_email = trim($matches[2]);
                } else {
                    $cc_email = $cc_recipient;
                    $cc_name = '';
                }

                if (filter_var($cc_email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($cc_email, $cc_name);
                }
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;

        foreach ($attachments as $att) {
            if (!empty($att['filename']) && !empty($att['content'])) {
                $mail->addStringAttachment($att['content'], $att['filename']);
            }
        }

        $mail->send();
        log_event("Email sent to $to" . (!empty($cc) ? " (cc: $cc)" : ''));
        return ['success' => true];
    } catch (Exception $e) {
        log_event("Email error: {$mail->ErrorInfo}", 'error');
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}