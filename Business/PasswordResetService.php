<?php
require_once __DIR__ . '/../Data/UserData.php';
require_once __DIR__ . '/MailService.php';

class PasswordResetService {
    private UserData $users;
    private MailService $mailer;
    private string $appUrl;
    private array $mailConfig;

    public function __construct(
        ?UserData $users = null,
        ?MailService $mailer = null,
        ?string $appUrl = null
    ) {
        $config = require __DIR__ . '/../config.php';
        $this->users = $users ?? new UserData();
        $this->mailer = $mailer ?? new MailService();
        $this->appUrl = rtrim($appUrl ?? (string)($config['app_url'] ?? ''), '/');
        $this->mailConfig = $config['mail'] ?? [];
    }

    public function requestReset(string $email): array {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }
        if (!filter_var($this->appUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'The application URL is not configured.'];
        }
        if (empty($this->mailConfig['username']) || empty($this->mailConfig['password'])) {
            return ['success' => false, 'error' => 'Email is not configured. Add your SMTP username and password to config.php.'];
        }

        $user = $this->users->findByEmail($email);
        if (!$user) return ['success' => true];

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + 3600);
        $this->users->savePasswordResetToken($user['userID'], $tokenHash, $expiresAt);

        $url = $this->appUrl . '/Presentation/auth/reset_password.php?token=' . rawurlencode($token);
        $safeName = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $html = '<p>Hello ' . $safeName . ',</p>'
              . '<p>We received a request to reset your DineNest account password.</p>'
              . '<p><a href="' . $safeUrl . '">Reset your password</a></p>'
              . '<p>This link expires in one hour. If you did not request this, you can ignore this email.</p>';

        $sent = $this->mailer->send(
            $user['email'],
            $user['name'],
            'Reset your DineNest password',
            $html
        );
        if (!$sent['success']) {
            $this->users->deletePasswordResetToken($user['userID'], $tokenHash);
            error_log('Password reset email delivery failed: ' . ($sent['error'] ?? 'Unknown email error.'));
            return ['success' => false, 'error' => $sent['error'] ?? 'Could not send the reset email.'];
        }

        return ['success' => true];
    }

    public function isTokenValid(string $token): bool {
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) return false;
        return $this->users->passwordResetTokenIsValid(hash('sha256', $token));
    }

    public function resetPassword(string $token, string $password, string $confirmPassword): array {
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return ['success' => false, 'error' => 'This reset link is invalid or has expired.'];
        }
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/', $password)) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters and contain letters and numbers.'];
        }
        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Passwords do not match.'];
        }

        $reset = $this->users->resetPassword(hash('sha256', $token), password_hash($password, PASSWORD_DEFAULT));
        if (!$reset) return ['success' => false, 'error' => 'This reset link is invalid or has expired.'];
        return ['success' => true];
    }
}
