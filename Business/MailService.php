<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public function send(string $to, string $toName, string $subject, string $html): array {
        $cfg = (require __DIR__ . '/../config.php')['mail'] ?? null;
        if (!$cfg || empty($cfg['username']) || empty($cfg['password'])) {
            return ['success' => false, 'error' => 'Email is not configured.'];
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Customer email address is invalid.'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $cfg['port'];
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 15;

            $mail->setFrom($cfg['username'], $cfg['from_name']);
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);
            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            $details = $mail->ErrorInfo ?: $e->getMessage();
            error_log('SMTP mail failure: ' . $details);
            if (stripos($details, 'Application-specific password required') !== false
                || stripos($details, 'InvalidSecondFactor') !== false
                || stripos($details, '534 5.7.9') !== false) {
                return [
                    'success' => false,
                    'error' => 'Gmail rejected the password. Enable 2-Step Verification and use a Google App Password in config.php.',
                ];
            }
            return [
                'success' => false,
                'error' => 'Could not send email. Check the SMTP host, port, username, and app password.',
            ];
        }
    }
}