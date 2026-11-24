<?php
require_once __DIR__ . '/Database.php';

class PaymentData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }
    public function pdo(): PDO { return $this->pdo; }
    public function begin(): void    { $this->pdo->beginTransaction(); }
    public function commit(): void   { $this->pdo->commit(); }
    public function rollBack(): void { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }

    public function findAll(): array {
        return $this->pdo->query(
            "SELECT p.*, u.name
             FROM payment p
             JOIN orders o ON p.orderID = o.orderID
             JOIN user u ON o.userID = u.userID
             ORDER BY p.paymentID DESC"
        )->fetchAll();
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.name
             FROM payment p
             JOIN orders o ON p.orderID = o.orderID
             JOIN user u ON o.userID = u.userID
             WHERE p.paymentID = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByOrder(string $orderID): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM payment WHERE orderID = ?");
        $stmt->execute([$orderID]);
        return $stmt->fetch() ?: null;
    }

    public function getOrder(string $orderID): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.name FROM orders o JOIN user u ON o.userID = u.userID WHERE o.orderID = ?"
        );
        $stmt->execute([$orderID]);
        return $stmt->fetch() ?: null;
    }

    /** 還沒付款（沒有付款紀錄，或付款還是 Pending）且沒被取消的訂單 */
    public function getPayableOrders(): array {
        return $this->pdo->query(
            "SELECT o.orderID, o.totalAmount, u.name, t.tableNumber
             FROM orders o
             JOIN user u ON o.userID = u.userID
             JOIN restaurant_table t ON o.tableID = t.tableID
             LEFT JOIN payment p ON p.orderID = o.orderID
             WHERE o.orderStatus <> 'Cancelled'
               AND (p.paymentID IS NULL OR p.paymentStatus = 'Pending')
             ORDER BY o.orderID"
        )->fetchAll();
    }

    public function insert(string $orderID, float $amount, string $method, ?string $onlineType, string $status): string {
        $id = Database::generateID($this->pdo, 'payment', 'paymentID', 'P', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO payment (paymentID, orderID, paymentAmount, paymentMethod, onlineType, paymentDate, paymentStatus)
             VALUES (?, ?, ?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([$id, $orderID, $amount, $method, $onlineType, $status]);
        return $id;
    }

    public function completePending(string $paymentID, float $amount, string $method, ?string $onlineType): void {
        $stmt = $this->pdo->prepare(
            "UPDATE payment SET paymentAmount = ?, paymentMethod = ?, onlineType = ?, paymentDate = NOW(), paymentStatus = 'Completed'
             WHERE paymentID = ?"
        );
        $stmt->execute([$amount, $method, $onlineType, $paymentID]);
    }

    public function updateMethod(string $id, string $method, ?string $onlineType): void {
        $stmt = $this->pdo->prepare("UPDATE payment SET paymentMethod = ?, onlineType = ? WHERE paymentID = ?");
        $stmt->execute([$method, $onlineType, $id]);
    }

    public function setStatus(string $id, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE payment SET paymentStatus = ? WHERE paymentID = ?");
        $stmt->execute([$status, $id]);
    }

    public function setOrderStatus(string $orderID, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE orders SET orderStatus = ? WHERE orderID = ?");
        $stmt->execute([$status, $orderID]);
    }
}