<?php
require_once __DIR__ . '/Database.php';

class ReceiptData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }

    public function exists(string $paymentID, string $type): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM receipt WHERE paymentID = ? AND receiptType = ?");
        $stmt->execute([$paymentID, $type]);
        return $stmt->fetchColumn() > 0;
    }

    public function insert(string $paymentID, string $type): string {
        $id = Database::generateID($this->pdo, 'receipt', 'receiptID', 'R', 4);
        $stmt = $this->pdo->prepare("INSERT INTO receipt (receiptID, paymentID, receiptType) VALUES (?, ?, ?)");
        $stmt->execute([$id, $paymentID, $type]);
        return $id;
    }

    public function findByPayment(string $paymentID, string $type): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM receipt WHERE paymentID = ? AND receiptType = ?");
        $stmt->execute([$paymentID, $type]);
        return $stmt->fetch() ?: null;
    }

    /** 收據表頭：付款 + 訂單 + 顧客 + 桌號（不重複存內容，查看時才 join） */
    public function getHeader(string $paymentID): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT p.paymentID, p.paymentAmount, p.paymentMethod, p.paymentDate, p.paymentStatus,
                    o.orderID, o.orderType, u.name AS customerName, t.tableNumber
             FROM payment p
             JOIN orders o ON p.orderID = o.orderID
             JOIN user u ON o.userID = u.userID
             JOIN restaurant_table t ON o.tableID = t.tableID
             WHERE p.paymentID = ?"
        );
        $stmt->execute([$paymentID]);
        return $stmt->fetch() ?: null;
    }

    public function getItems(string $orderID): array {
        $stmt = $this->pdo->prepare(
            "SELECT m.foodName, oi.quantity, oi.unitPrice, oi.subtotal
             FROM orderitem oi JOIN menuitem m ON oi.menuItemID = m.menuItemID
             WHERE oi.orderID = ? ORDER BY oi.orderItemID"
        );
        $stmt->execute([$orderID]);
        return $stmt->fetchAll();
    }
}