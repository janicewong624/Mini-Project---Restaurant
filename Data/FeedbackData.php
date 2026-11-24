<?php
require_once __DIR__ . '/Database.php';

class FeedbackData {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function userExists(string $userID): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user WHERE userID = ?");
        $stmt->execute([$userID]);
        return $stmt->fetchColumn() > 0;
    }

    public function insert(string $userID, int $rating, string $comment): string {
        $id = Database::generateID($this->pdo, 'feedback', 'feedbackID', 'F', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO feedback (feedbackID, userID, rating, comment) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$id, $userID, $rating, $comment]);
        return $id;
    }

    public function findAll(): array {
        $sql = "SELECT f.feedbackID, f.userID, u.name, f.rating, f.comment, f.feedbackDate
                FROM feedback f
                JOIN user u ON f.userID = u.userID
                ORDER BY f.feedbackDate DESC, f.feedbackID DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT f.feedbackID, f.userID, u.name, f.rating, f.comment, f.feedbackDate
             FROM feedback f JOIN user u ON f.userID = u.userID
             WHERE f.feedbackID = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function update(string $id, int $rating, string $comment): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE feedback SET rating = ?, comment = ? WHERE feedbackID = ?"
        );
        return $stmt->execute([$rating, $comment, $id]);
    }

    public function delete(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM feedback WHERE feedbackID = ?");
        return $stmt->execute([$id]);
    }
}