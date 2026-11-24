<?php
require_once __DIR__ . '/Database.php';

class UserData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) return null;

        return [
            'userID' => (string)$user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'password' => $user['password'],
            'role' => strtolower((string)$user['role']) === 'admin' ? 'Administrator' : 'Customer',
        ];
    }

    public function emailExists(string $email, ?string $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getCustomers(): array {
        return $this->pdo->query(
            "SELECT id AS userID, full_name AS name, email, phone AS contactNumber, created_at
             FROM users WHERE role = 'customer' ORDER BY full_name, id"
        )->fetchAll();
    }

    public function findCustomer(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT id AS userID, full_name AS name, email, phone AS contactNumber, created_at
             FROM users WHERE id = ? AND role = 'customer' LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCustomer(
        string $name,
        string $email,
        string $contactNumber,
        string $passwordHash
    ): string {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password, email_verified, role)
             VALUES (?, ?, ?, ?, 0, 'customer')"
        );
        $stmt->execute([$name, $email, $contactNumber, $passwordHash]);
        return $this->pdo->lastInsertId();
    }

    public function updateCustomer(string $id, string $name, string $email, string $contactNumber): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET full_name = ?, email = ?, phone = ?
             WHERE id = ? AND role = 'customer'"
        );
        $stmt->execute([$name, $email, $contactNumber, $id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteCustomer(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function savePasswordResetToken(string $userID, string $tokenHash, string $expiresAt): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 token_hash = VALUES(token_hash),
                 expires_at = VALUES(expires_at),
                 created_at = CURRENT_TIMESTAMP"
        );
        $stmt->execute([$userID, $tokenHash, $expiresAt]);
    }

    public function deletePasswordResetToken(string $userID, string $tokenHash): void {
        $stmt = $this->pdo->prepare(
            "DELETE FROM password_reset_tokens WHERE user_id = ? AND token_hash = ?"
        );
        $stmt->execute([$userID, $tokenHash]);
    }

    public function passwordResetTokenIsValid(string $tokenHash): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM password_reset_tokens
             WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP()"
        );
        $stmt->execute([$tokenHash]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function resetPassword(string $tokenHash, string $passwordHash): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT user_id FROM password_reset_tokens
                 WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP() FOR UPDATE"
            );
            $stmt->execute([$tokenHash]);
            $userID = $stmt->fetchColumn();

            if ($userID === false) {
                $this->pdo->commit();
                return false;
            }

            $update = $this->pdo->prepare(
                "UPDATE users SET password = ?, email_verified = 1 WHERE id = ?"
            );
            $update->execute([$passwordHash, $userID]);

            $delete = $this->pdo->prepare(
                "DELETE FROM password_reset_tokens WHERE user_id = ? AND token_hash = ?"
            );
            $delete->execute([$userID, $tokenHash]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }
}