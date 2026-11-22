<?php
require_once __DIR__ . '/Database.php';

class TableData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }

    public function findAll(): array {
        return $this->pdo->query(
            "SELECT t.tableID, t.tableNumber, t.seatingCapacity, t.tableStatus,
                    (SELECT COUNT(*) FROM orders o
                      WHERE o.tableID = t.tableID AND o.orderStatus = 'Pending') AS activeOrders
             FROM restaurant_table t
             ORDER BY CAST(t.tableNumber AS UNSIGNED), t.tableNumber"
        )->fetchAll();
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurant_table WHERE tableID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** 用桌號找桌子；編輯時可排除自己（檢查桌號有沒有重複） */
    public function findByNumber(string $number, ?string $excludeID = null): ?array {
        $sql = "SELECT * FROM restaurant_table WHERE tableNumber = ?";
        $params = [$number];
        if ($excludeID !== null) {
            $sql .= " AND tableID <> ?";
            $params[] = $excludeID;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function insert(string $number, int $capacity, string $status): string {
        $id = Database::generateID($this->pdo, 'restaurant_table', 'tableID', 'T', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO restaurant_table (tableID, tableNumber, seatingCapacity, tableStatus) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$id, $number, $capacity, $status]);
        return $id;
    }

    public function update(string $id, string $number, int $capacity, string $status): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE restaurant_table SET tableNumber = ?, seatingCapacity = ?, tableStatus = ? WHERE tableID = ?"
        );
        return $stmt->execute([$number, $capacity, $status, $id]);
    }

    public function delete(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_table WHERE tableID = ?");
        return $stmt->execute([$id]);
    }

    /** 這張桌子被多少張訂單用過（有訂單紀錄就不能刪除） */
    public function countOrders(string $id): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE tableID = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * 依「進行中的內用訂單」自動更新桌子狀態：
     * 有 Pending 的 Dine-in 訂單 -> Occupied，沒有 -> Available。
     * 只會動 Available / Occupied，其他狀態（例如 Reserved）不會被改。
     * 這樣 Member A 的付款功能把訂單改成 Completed 後，桌子也會自動空出來。
     */
    public function syncStatuses(): void {
        $this->pdo->exec(
            "UPDATE restaurant_table
             SET tableStatus = CASE
                 WHEN EXISTS (SELECT 1 FROM orders o
                               WHERE o.tableID = restaurant_table.tableID
                                 AND o.orderType = 'Dine-in'
                                 AND o.orderStatus = 'Pending')
                 THEN 'Occupied' ELSE 'Available' END
             WHERE tableStatus IN ('Available', 'Occupied')"
        );
    }
}
