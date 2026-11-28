<?php
require_once __DIR__ . '/Database.php';

class FeedbackData {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function userExists(string $userID): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
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
        $sql = "SELECT f.feedbackID, f.userID, u.full_name AS name, f.rating, f.comment, f.feedbackDate
                FROM feedback f
                JOIN users u ON f.userID = u.id
                ORDER BY f.feedbackDate DESC, f.feedbackID DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT f.feedbackID, f.userID, u.full_name AS name, f.rating, f.comment, f.feedbackDate
             FROM feedback f JOIN users u ON f.userID = u.id
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

    public function findRecent(int $limit = 12, int $minRating = 1): array {
        $stmt = $this->pdo->prepare(
            "SELECT u.full_name AS name, f.rating, f.comment, f.feedbackDate
             FROM feedback f JOIN users u ON f.userID = u.id
             WHERE f.rating >= ? AND f.comment <> ''
             ORDER BY f.feedbackDate DESC LIMIT " . (int)$limit
        );
        $stmt->execute([$minRating]);
        return $stmt->fetchAll();
    }

    public function summary(): array {
        $row = $this->pdo->query("SELECT COUNT(*) c, COALESCE(AVG(rating),0) a FROM feedback")->fetch();
        return ['count' => (int)$row['c'], 'avg' => round((float)$row['a'], 1)];
    }

    public function findFiltered(?int $rating, ?string $from, ?string $to): array {
    $sql = "SELECT f.feedbackID, f.userID, u.full_name AS name, f.rating, f.comment, f.feedbackDate
            FROM feedback f JOIN users u ON f.userID = u.id WHERE 1=1";
    $p = [];
    if ($rating)  { $sql .= " AND f.rating = ?";              $p[] = $rating; }
    if ($from)    { $sql .= " AND DATE(f.feedbackDate) >= ?"; $p[] = $from; }
    if ($to)      { $sql .= " AND DATE(f.feedbackDate) <= ?"; $p[] = $to; }
    $sql .= " ORDER BY f.feedbackDate DESC, f.feedbackID DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($p);
    return $stmt->fetchAll();
}

public function distribution(?string $from, ?string $to): array {
    $sql = "SELECT rating, COUNT(*) c FROM feedback WHERE 1=1";
    $p = [];
    if ($from) { $sql .= " AND DATE(feedbackDate) >= ?"; $p[] = $from; }
    if ($to)   { $sql .= " AND DATE(feedbackDate) <= ?"; $p[] = $to; }
    $sql .= " GROUP BY rating";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($p);
    $out = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    foreach ($stmt->fetchAll() as $r) $out[(int)$r['rating']] = (int)$r['c'];
    return $out;
}
}