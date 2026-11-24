<?php
require_once __DIR__ . '/Database.php';

/**
 * 報表用的查詢（只讀）。日期範圍一律是 [from 00:00, toExclusive 00:00)。
 * - Order Summary 以「訂單日期 orderDate」為準
 * - Sales Report 以「付款日期 paymentDate」為準，而且只算 paymentStatus = 'Completed'（已收款）
 */
class ReportData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }

    private function rows(string $sql, array $params): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ===================== Order Summary =====================

    public function orderTotals(string $from, string $to): array {
        $rows = $this->rows(
            "SELECT COUNT(*) AS totalOrders,
                    COALESCE(SUM(CASE WHEN orderStatus = 'Pending'   THEN 1 ELSE 0 END), 0) AS pending,
                    COALESCE(SUM(CASE WHEN orderStatus = 'Completed' THEN 1 ELSE 0 END), 0) AS completed,
                    COALESCE(SUM(CASE WHEN orderStatus = 'Cancelled' THEN 1 ELSE 0 END), 0) AS cancelled,
                    COALESCE(SUM(CASE WHEN orderType = 'Dine-in'  AND orderStatus <> 'Cancelled' THEN 1 ELSE 0 END), 0) AS dineIn,
                    COALESCE(SUM(CASE WHEN orderType = 'Takeaway' AND orderStatus <> 'Cancelled' THEN 1 ELSE 0 END), 0) AS takeaway,
                    COALESCE(SUM(CASE WHEN orderStatus <> 'Cancelled' THEN totalAmount ELSE 0 END), 0) AS orderValue
             FROM orders
             WHERE orderDate >= ? AND orderDate < ?",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
        return $rows[0];
    }

    public function ordersByDay(string $from, string $to): array {
        return $this->rows(
            "SELECT DATE(orderDate) AS day, COUNT(*) AS orders,
                    COALESCE(SUM(CASE WHEN orderStatus <> 'Cancelled' THEN totalAmount ELSE 0 END), 0) AS value
             FROM orders
             WHERE orderDate >= ? AND orderDate < ?
             GROUP BY DATE(orderDate)
             ORDER BY day",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    /** 最熱門的菜（不含已取消的訂單） */
    public function topItemsByQuantity(string $from, string $to, int $limit = 10): array {
        return $this->rows(
            "SELECT m.foodName, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS amount
             FROM orderitem oi
             JOIN orders o ON oi.orderID = o.orderID
             JOIN menuitem m ON oi.menuItemID = m.menuItemID
             WHERE o.orderStatus <> 'Cancelled' AND o.orderDate >= ? AND o.orderDate < ?
             GROUP BY m.menuItemID, m.foodName
             ORDER BY qty DESC, m.foodName
             LIMIT " . (int)$limit,
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    /** 各桌用餐次數（內用、不含已取消） */
    public function tableUsage(string $from, string $to, int $limit = 10): array {
        return $this->rows(
            "SELECT t.tableNumber, COUNT(*) AS orders,
                    COALESCE(SUM(o.totalAmount), 0) AS value
             FROM orders o
             JOIN restaurant_table t ON o.tableID = t.tableID
             WHERE o.orderType = 'Dine-in' AND o.orderStatus <> 'Cancelled'
               AND o.orderDate >= ? AND o.orderDate < ?
             GROUP BY t.tableID, t.tableNumber
             ORDER BY orders DESC, t.tableNumber
             LIMIT " . (int)$limit,
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    // ===================== Sales Report =====================

    public function salesTotals(string $from, string $to): array {
        $rows = $this->rows(
            "SELECT COUNT(*) AS payments, COALESCE(SUM(paymentAmount), 0) AS revenue
             FROM payment
             WHERE paymentStatus = 'Completed' AND paymentDate >= ? AND paymentDate < ?",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
        return $rows[0];
    }

    /** 依日 / 月統計營業額。$group 只接受 'day' 或 'month'（不是使用者字串直接放進 SQL） */
    public function salesByPeriod(string $from, string $to, string $group): array {
        $expr = $group === 'month' ? "DATE_FORMAT(paymentDate, '%Y-%m')" : "DATE(paymentDate)";
        return $this->rows(
            "SELECT $expr AS period, COUNT(*) AS payments, COALESCE(SUM(paymentAmount), 0) AS revenue
             FROM payment
             WHERE paymentStatus = 'Completed' AND paymentDate >= ? AND paymentDate < ?
             GROUP BY $expr
             ORDER BY period",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    public function salesByMethod(string $from, string $to): array {
        return $this->rows(
            "SELECT COALESCE(paymentMethod, '-') AS method, COUNT(*) AS payments, COALESCE(SUM(paymentAmount), 0) AS revenue
             FROM payment
             WHERE paymentStatus = 'Completed' AND paymentDate >= ? AND paymentDate < ?
             GROUP BY paymentMethod
             ORDER BY revenue DESC",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    public function salesByOrderType(string $from, string $to): array {
        return $this->rows(
            "SELECT o.orderType AS type, COUNT(*) AS payments, COALESCE(SUM(p.paymentAmount), 0) AS revenue
             FROM payment p
             JOIN orders o ON p.orderID = o.orderID
             WHERE p.paymentStatus = 'Completed' AND p.paymentDate >= ? AND p.paymentDate < ?
             GROUP BY o.orderType
             ORDER BY revenue DESC",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    public function salesByCategory(string $from, string $to): array {
        return $this->rows(
            "SELECT COALESCE(m.category, 'Others') AS category, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
             FROM payment p
             JOIN orderitem oi ON oi.orderID = p.orderID
             JOIN menuitem m ON oi.menuItemID = m.menuItemID
             WHERE p.paymentStatus = 'Completed' AND p.paymentDate >= ? AND p.paymentDate < ?
             GROUP BY m.category
             ORDER BY revenue DESC",
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }

    public function bestSellers(string $from, string $to, int $limit = 10): array {
        return $this->rows(
            "SELECT m.foodName, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
             FROM payment p
             JOIN orderitem oi ON oi.orderID = p.orderID
             JOIN menuitem m ON oi.menuItemID = m.menuItemID
             WHERE p.paymentStatus = 'Completed' AND p.paymentDate >= ? AND p.paymentDate < ?
             GROUP BY m.menuItemID, m.foodName
             ORDER BY revenue DESC, m.foodName
             LIMIT " . (int)$limit,
            [$from . ' 00:00:00', $to . ' 00:00:00']
        );
    }
}
