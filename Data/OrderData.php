<?php
require_once __DIR__ . '/Database.php';

class OrderData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }
    public function pdo(): PDO { return $this->pdo; }
    public function begin(): void    { $this->pdo->beginTransaction(); }
    public function commit(): void   { $this->pdo->commit(); }
    public function rollBack(): void { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }

    // ---------- 菜單 / 顧客 / 桌子（只讀，不改 Member C 的資料） ----------

    public function getMenuItems(): array {
        return $this->pdo->query(
            "SELECT menuItemID, foodName, description, price, category, image
             FROM menuitem ORDER BY category, foodName"
        )->fetchAll();
    }

    /** @param string[] $ids */
    public function getMenuItemsByIds(array $ids): array {
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT menuItemID, foodName, price FROM menuitem WHERE menuItemID IN ($in)"
        );
        $stmt->execute(array_values($ids));
        $out = [];
        foreach ($stmt->fetchAll() as $row) $out[$row['menuItemID']] = $row;
        return $out;
    }

    /** 顧客清單（不選 password） */
    public function getCustomers(): array {
        return $this->pdo->query(
            "SELECT id AS userID, full_name AS name, email, phone AS contactNumber
             FROM users WHERE role = 'customer' ORDER BY full_name"
        )->fetchAll();
    }

    public function userExists(string $userID): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'customer'");
        $stmt->execute([$userID]);
        return $stmt->fetchColumn() > 0;
    }

    public function findTableByNumber(string $number): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurant_table WHERE tableNumber = ?");
        $stmt->execute([$number]);
        return $stmt->fetch() ?: null;
    }

    /** 這張桌子上有沒有「別的顧客」進行中的內用訂單 */
    public function tableInUseByOthers(string $tableID, string $userID): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM orders
             WHERE tableID = ? AND orderType = 'Dine-in' AND orderStatus = 'Pending' AND userID <> ?"
        );
        $stmt->execute([$tableID, $userID]);
        return $stmt->fetchColumn() > 0;
    }

    /** 這位顧客目前進行中的內用桌號（用來讓他加點餐時直接沿用同一桌） */
    public function findMyActiveTableNumbers(string $userID): array {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT t.tableNumber
             FROM orders o JOIN restaurant_table t ON o.tableID = t.tableID
             WHERE o.userID = ? AND o.orderType = 'Dine-in' AND o.orderStatus = 'Pending'"
        );
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** 這位顧客「進行中、還沒付款」的內用訂單（可以往裡面加點餐） */
    public function findMyOpenDineInOrders(string $userID): array {
        $stmt = $this->pdo->prepare(
            "SELECT o.orderID, o.totalAmount, t.tableNumber
             FROM orders o
             JOIN restaurant_table t ON o.tableID = t.tableID
             LEFT JOIN payment p ON p.orderID = o.orderID
             WHERE o.userID = ? AND o.orderType = 'Dine-in' AND o.orderStatus = 'Pending'
               AND (p.paymentStatus IS NULL OR p.paymentStatus = 'Pending')
             ORDER BY o.orderDate, o.orderID"
        );
        $stmt->execute([$userID]);
        return $stmt->fetchAll();
    }

    public function updateItemQuantity(string $orderItemID, int $qty, float $subtotal): void {
        $stmt = $this->pdo->prepare("UPDATE orderitem SET quantity = ?, subtotal = ? WHERE orderItemID = ?");
        $stmt->execute([$qty, $subtotal, $orderItemID]);
    }

    // ---------- 訂單 ----------

    private function orderSelect(): string {
        // 外送/外帶訂單沒有桌子 -> 用 LEFT JOIN，不要用 INNER JOIN
        return "SELECT o.orderID, o.userID, u.full_name AS customerName, o.tableID, t.tableNumber,
                       o.orderDate, o.orderType, o.totalAmount, o.orderStatus,
                       p.paymentID, p.paymentStatus, p.paymentMethod
                FROM orders o
                JOIN users u ON o.userID = u.id
                LEFT JOIN restaurant_table t ON o.tableID = t.tableID
                LEFT JOIN payment p ON p.orderID = o.orderID";
    }

    public function findOrder(string $id): ?array {
        $stmt = $this->pdo->prepare($this->orderSelect() . " WHERE o.orderID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findItems(string $orderID): array {
        $stmt = $this->pdo->prepare(
            "SELECT oi.orderItemID, oi.menuItemID, m.foodName, oi.quantity, oi.unitPrice, oi.subtotal
             FROM orderitem oi
             JOIN menuitem m ON oi.menuItemID = m.menuItemID
             WHERE oi.orderID = ? ORDER BY oi.orderItemID"
        );
        $stmt->execute([$orderID]);
        return $stmt->fetchAll();
    }

    public function findByUser(string $userID): array {
        $stmt = $this->pdo->prepare(
            $this->orderSelect() . " WHERE o.userID = ? ORDER BY o.orderDate DESC, o.orderID DESC"
        );
        $stmt->execute([$userID]);
        return $stmt->fetchAll();
    }

    /** @param array{status?:string,type?:string,from?:string,toExclusive?:string} $f  日期格式 Y-m-d；toExclusive = 結束日的隔天 */
    public function findAll(array $f = []): array {
        $where = [];
        $params = [];
        if (!empty($f['status'])) { $where[] = "o.orderStatus = ?"; $params[] = $f['status']; }
        if (!empty($f['type']))   { $where[] = "o.orderType = ?";   $params[] = $f['type']; }
        if (!empty($f['from']))   { $where[] = "o.orderDate >= ?";  $params[] = $f['from'] . ' 00:00:00'; }
        if (!empty($f['toExclusive'])) { $where[] = "o.orderDate < ?"; $params[] = $f['toExclusive'] . ' 00:00:00'; }

        $sql = $this->orderSelect();
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY o.orderDate DESC, o.orderID DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function insertOrder(string $userID, ?string $tableID, string $type, float $total): string {
        $id = Database::generateID($this->pdo, 'orders', 'orderID', 'O', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (orderID, userID, tableID, orderDate, orderType, totalAmount, orderStatus)
             VALUES (?, ?, ?, NOW(), ?, ?, 'Pending')"
        );
        $stmt->execute([$id, $userID, $tableID, $type, $total]);
        return $id;
    }

    public function updateOrder(string $id, ?string $tableID, string $type, float $total): void {
        $stmt = $this->pdo->prepare(
            "UPDATE orders SET tableID = ?, orderType = ?, totalAmount = ? WHERE orderID = ?"
        );
        $stmt->execute([$tableID, $type, $total, $id]);
    }

    public function setStatus(string $id, string $status): void {
        $stmt = $this->pdo->prepare("UPDATE orders SET orderStatus = ? WHERE orderID = ?");
        $stmt->execute([$status, $id]);
    }

    public function deleteOrder(string $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM orders WHERE orderID = ?");
        $stmt->execute([$id]);
    }

    // ---------- 訂單明細 ----------

    public function insertItem(string $orderID, string $menuItemID, int $qty, float $unitPrice, float $subtotal): string {
        $id = Database::generateID($this->pdo, 'orderitem', 'orderItemID', 'OI', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO orderitem (orderItemID, orderID, menuItemID, quantity, unitPrice, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $orderID, $menuItemID, $qty, $unitPrice, $subtotal]);
        return $id;
    }

    public function deleteItems(string $orderID): void {
        $stmt = $this->pdo->prepare("DELETE FROM orderitem WHERE orderID = ?");
        $stmt->execute([$orderID]);
    }

    // ---------- 付款（只處理「還沒付款」的 Pending 付款；真正收款是 Member A 的功能） ----------

    /**
     * 下單時先建立一筆 Pending 付款（Member A 的 PaymentService 會用 completePending 完成它）。
     * 如果 payment 表不允許這樣新增（例如 paymentMethod 不能是 NULL），就略過，
     * 付款功能仍然可以在收款時自己新增一筆，不影響下單。
     */
    public function insertPendingPayment(string $orderID, float $amount): void {
        try {
            $id = Database::generateID($this->pdo, 'payment', 'paymentID', 'P', 4);
            $stmt = $this->pdo->prepare(
                "INSERT INTO payment (paymentID, orderID, paymentAmount, paymentMethod, paymentDate, paymentStatus)
                 VALUES (?, ?, ?, NULL, NOW(), 'Pending')"
            );
            $stmt->execute([$id, $orderID, $amount]);
        } catch (Throwable $ex) {
            // 略過：見上面說明
        }
    }

    public function updatePendingPaymentAmount(string $orderID, float $amount): void {
        $stmt = $this->pdo->prepare(
            "UPDATE payment SET paymentAmount = ? WHERE orderID = ? AND paymentStatus = 'Pending'"
        );
        $stmt->execute([$amount, $orderID]);
    }

    public function cancelPendingPayment(string $orderID): void {
        $stmt = $this->pdo->prepare(
            "UPDATE payment SET paymentStatus = 'Cancelled' WHERE orderID = ? AND paymentStatus = 'Pending'"
        );
        $stmt->execute([$orderID]);
    }

    /** 這張訂單的付款有沒有收據（有收據 = 有正式紀錄，不能刪除訂單） */
    public function countReceipts(string $orderID): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM receipt r JOIN payment p ON r.paymentID = p.paymentID WHERE p.orderID = ?"
        );
        $stmt->execute([$orderID]);
        return (int)$stmt->fetchColumn();
    }

    public function deletePayments(string $orderID): void {
        $stmt = $this->pdo->prepare("DELETE FROM payment WHERE orderID = ?");
        $stmt->execute([$orderID]);
    }
}
