<?php
require_once __DIR__ . '/../Data/OrderData.php';
require_once __DIR__ . '/../Data/TableData.php';

class OrderService {
    const TYPES    = ['Dine-in', 'Takeaway'];
    const STATUSES = ['Pending', 'Completed', 'Cancelled'];   // 跟 Member A 的付款功能用同一組狀態
    const MAX_QTY  = 99;

    private OrderData $data;
    private TableData $tables;

    public function __construct() {
        $this->data   = new OrderData();
        $this->tables = new TableData($this->data->pdo());   // 共用同一個連線，交易才會一起 commit
    }

    // ---------- 讀取 ----------

    public function getMenu(): array      { return $this->data->getMenuItems(); }
    public function getCustomers(): array { return $this->data->getCustomers(); }

    public function getAvailableTableNumbers(): array {
        $this->tables->syncStatuses();
        $out = [];
        foreach ($this->tables->findAll() as $t) {
            if ($t['tableStatus'] === 'Available') $out[] = $t['tableNumber'];
        }
        return $out;
    }

    /** 這位顧客目前正在用的桌號（還有 Pending 內用訂單） */
    public function getMyActiveTables(string $userID): array { return $this->data->findMyActiveTableNumbers($userID); }

    public function getMyOpenOrders(string $userID): array { return $this->data->findMyOpenDineInOrders($userID); }

    public function getByUser(string $userID): array   { return $this->data->findByUser($userID); }

    /** admin 訂單列表的篩選（狀態 / 方式 / 日期範圍），不合法的條件直接忽略 */
    public function search(array $in): array {
        $f = [];
        if (in_array($in['status'] ?? '', self::STATUSES, true)) $f['status'] = $in['status'];
        if (in_array($in['type'] ?? '', self::TYPES, true))      $f['type']   = $in['type'];

        $from = self::validDate((string)($in['from'] ?? ''));
        $to   = self::validDate((string)($in['to'] ?? ''));
        if ($from) $f['from'] = $from;
        if ($to)   $f['toExclusive'] = date('Y-m-d', strtotime($to . ' +1 day'));
        return $this->data->findAll($f);
    }

    /** 'YYYY-MM-DD' 而且是真的有這一天才回傳，否則 null */
    public static function validDate(string $s): ?string {
        $d = DateTime::createFromFormat('Y-m-d', $s);
        return ($d && $d->format('Y-m-d') === $s) ? $s : null;
    }

    /**
     * 訂單 + 明細。$onlyUserID 有值時（顧客）只能看自己的訂單，別人的回傳 null。
     * @return array{order:array,items:array}|null
     */
    public function getDetail(string $orderID, ?string $onlyUserID = null): ?array {
        $order = $this->data->findOrder($orderID);
        if (!$order) return null;
        if ($onlyUserID !== null && (string)$order['userID'] !== $onlyUserID) return null;
        return ['order' => $order, 'items' => $this->data->findItems($orderID)];
    }

    // ---------- 數量 / 購物車 ----------

    /**
     * 把表單送來的 qty[menuItemID] => 數量 整理成 [menuItemID => int]（數量 0 或空白 = 不要，會被拿掉）
     * @return array{0:array<string,int>,1:string[]} [購物車, 錯誤訊息]
     */
    public function normalizeQuantities(array $raw): array {
        $cart = [];
        $errors = [];
        foreach ($raw as $id => $q) {
            $q = is_scalar($q) ? trim((string)$q) : '';
            if ($q === '') continue;
            if (!ctype_digit($q)) {
                $errors[] = "Quantity must be a whole number (0 or more).";
                break;
            }
            $n = (int)$q;
            if ($n > self::MAX_QTY) {
                $errors[] = "You can order at most " . self::MAX_QTY . " of each item.";
                break;
            }
            if ($n > 0) $cart[(string)$id] = $n;
        }
        return [$cart, $errors];
    }

    /**
     * 購物車 -> 明細行 + 總額（價格一律用資料庫現在的價格）
     * @return array{0:array,1:float,2:string[]} [lines, total, errors]
     */
    public function buildLines(array $cart): array {
        if (!$cart) return [[], 0.0, ["Your order is empty. Please select at least one item."]];

        $menu = $this->data->getMenuItemsByIds(array_keys($cart));
        $lines = [];
        $errors = [];
        $total = 0.0;
        foreach ($cart as $id => $qty) {
            if (!isset($menu[$id])) {
                $errors[] = "One of the selected items is no longer available.";
                continue;
            }
            $price    = (float)$menu[$id]['price'];
            $subtotal = round($price * $qty, 2);
            $total   += $subtotal;
            $lines[] = [
                'menuItemID' => $id,
                'foodName'   => $menu[$id]['foodName'],
                'unitPrice'  => $price,
                'quantity'   => $qty,
                'subtotal'   => $subtotal,
            ];
        }
        return [$lines, round($total, 2), $errors];
    }

    // ---------- 下單 / 修改 ----------

    /**
     * 驗證「明細 + 用餐方式 + 桌號」。
     * Dine-in 一定要有桌號，且那張桌子要存在、是空的；Takeaway 不需要桌號（tableID = NULL）。
     * @param array|null $current 修改訂單時傳入原本的訂單（原本的桌子不算被佔用）
     * @return array{0:string[],1:array,2:float,3:?string} [errors, lines, total, tableID]
     */
    private function prepare(array $cart, string $type, string $tableNumber, ?array $current = null, ?string $userID = null): array {
        [$lines, $total, $errors] = $this->buildLines($cart);

        if (!in_array($type, self::TYPES, true)) {
            $errors[] = "Please choose Dine-in or Takeaway.";
        }

        $tableID = null;
        if ($type === 'Dine-in') {
            $tableNumber = trim($tableNumber);
            if ($tableNumber === '') {
                $errors[] = "Please enter your table number for dine-in.";
            } else {
                $this->tables->syncStatuses();
                $t = $this->data->findTableByNumber($tableNumber);
                if (!$t) {
                    $errors[] = "Table $tableNumber does not exist.";
                } else {
                    $sameAsBefore = $current !== null && $current['tableID'] === $t['tableID'];
                    // 同一位顧客加點餐：桌子雖然是 Occupied，但只有他自己在用，就允許沿用
                    $myOwnTable = $userID !== null
                        && $t['tableStatus'] === 'Occupied'
                        && !$this->data->tableInUseByOthers($t['tableID'], $userID);
                    if (!$sameAsBefore && !$myOwnTable && $t['tableStatus'] !== 'Available') {
                        $errors[] = "Table $tableNumber is currently {$t['tableStatus']}. Please choose another table.";
                    } else {
                        $tableID = $t['tableID'];
                    }
                }
            }
        }
        return [$errors, $lines, $total, $tableID];
    }

    public function place(string $userID, array $cart, string $type, string $tableNumber): array {
        if (!$this->data->userExists($userID)) return $this->fail("Customer does not exist.");

        [$errors, $lines, $total, $tableID] = $this->prepare($cart, $type, $tableNumber, null, $userID);
        if ($errors) return ['success' => false, 'errors' => $errors];

        try {
            $this->data->begin();
            $orderID = $this->data->insertOrder($userID, $tableID, $type, $total);
            foreach ($lines as $l) {
                $this->data->insertItem($orderID, $l['menuItemID'], $l['quantity'], $l['unitPrice'], $l['subtotal']);
            }
            $this->data->insertPendingPayment($orderID, $total);
            $this->tables->syncStatuses();
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Could not place the order, nothing was saved. Please try again.");
        }
        return ['success' => true, 'errors' => [], 'id' => $orderID];
    }

    /**
     * 顧客加點餐：把新的菜合併進「自己進行中、還沒付款」的訂單。
     * 同一道菜 -> 數量相加（沿用原本的單價）；新的菜 -> 新增一行。
     * 總額和 Pending 付款金額一起更新，所以結帳時還是只有一張帳單。
     */
    public function addToOrder(string $orderID, string $userID, array $cart): array {
        $order = $this->data->findOrder($orderID);
        if (!$order || (string)$order['userID'] !== $userID) return $this->fail("Order not found.");
        if ($order['orderStatus'] !== 'Pending' || ($order['paymentStatus'] ?? '') === 'Completed') {
            return $this->fail("This order is already paid or closed, so items can no longer be added. Please start a new order.");
        }

        [$lines, , $errors] = $this->buildLines($cart);
        if ($errors) return ['success' => false, 'errors' => $errors];

        $existing = [];
        foreach ($this->data->findItems($orderID) as $it) $existing[(string)$it['menuItemID']] = $it;
        foreach ($lines as $l) {
            $key = (string)$l['menuItemID'];
            if (isset($existing[$key]) && (int)$existing[$key]['quantity'] + $l['quantity'] > self::MAX_QTY) {
                return $this->fail("You can order at most " . self::MAX_QTY . " of each item ({$l['foodName']}).");
            }
        }

        try {
            $this->data->begin();
            foreach ($lines as $l) {
                $key = (string)$l['menuItemID'];
                if (isset($existing[$key])) {
                    $newQty = (int)$existing[$key]['quantity'] + $l['quantity'];
                    $this->data->updateItemQuantity(
                        $existing[$key]['orderItemID'], $newQty,
                        round((float)$existing[$key]['unitPrice'] * $newQty, 2)
                    );
                } else {
                    $this->data->insertItem($orderID, $l['menuItemID'], $l['quantity'], $l['unitPrice'], $l['subtotal']);
                }
            }
            $newTotal = 0.0;
            foreach ($this->data->findItems($orderID) as $it) $newTotal += (float)$it['subtotal'];
            $newTotal = round($newTotal, 2);

            $this->data->updateOrder($orderID, $order['tableID'], $order['orderType'], $newTotal);
            $this->data->updatePendingPaymentAmount($orderID, $newTotal);
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Could not add the items, nothing was changed. Please try again.");
        }
        return ['success' => true, 'errors' => [], 'id' => $orderID];
    }

    /** 修改訂單（只有 Pending 的訂單可以改）：重算明細、總額、桌子，並同步 Pending 付款的金額 */
    public function update(string $orderID, array $cart, string $type, string $tableNumber): array {
        $order = $this->data->findOrder($orderID);
        if (!$order) return $this->fail("Order not found.");
        if ($order['orderStatus'] !== 'Pending') {
            return $this->fail("Only Pending orders can be edited. This order is {$order['orderStatus']}.");
        }

        [$errors, $lines, $total, $tableID] = $this->prepare($cart, $type, $tableNumber, $order, (string)$order['userID']);
        if ($errors) return ['success' => false, 'errors' => $errors];

        try {
            $this->data->begin();
            $this->data->updateOrder($orderID, $tableID, $type, $total);
            $this->data->deleteItems($orderID);
            foreach ($lines as $l) {
                $this->data->insertItem($orderID, $l['menuItemID'], $l['quantity'], $l['unitPrice'], $l['subtotal']);
            }
            $this->data->updatePendingPaymentAmount($orderID, $total);
            $this->tables->syncStatuses();
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Could not update the order, nothing was changed. Please try again.");
        }
        return ['success' => true, 'errors' => []];
    }

    // ---------- 取消 / 刪除 ----------

    /** 取消訂單。$byUserID 有值（顧客取消）時，只能取消自己的訂單 */
    public function cancel(string $orderID, ?string $byUserID = null): array {
        $order = $this->data->findOrder($orderID);
        if (!$order) return $this->fail("Order not found.");
        if ($byUserID !== null && (string)$order['userID'] !== $byUserID) {
            return $this->fail("You can only cancel your own orders.");
        }
        if ($order['orderStatus'] === 'Cancelled') return $this->fail("This order is already cancelled.");
        if ($order['orderStatus'] === 'Completed') {
            return $this->fail("This order has already been paid. Cancel the payment first, then cancel the order.");
        }

        try {
            $this->data->begin();
            $this->data->setStatus($orderID, 'Cancelled');
            $this->data->cancelPendingPayment($orderID);
            $this->tables->syncStatuses();
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Cancel failed. Please try again.");
        }
        return ['success' => true, 'errors' => []];
    }

    /**
     * admin 直接改訂單狀態。訂單狀態和付款是分開的：送完餐就可以標 Completed，不一定要先收款。
     * 只有一種情況不能改：已經有「已收款」的付款紀錄，要先到 Payments 取消付款。
     */
    public function changeStatus(string $orderID, string $to): array {
        $order = $this->data->findOrder($orderID);
        if (!$order) return $this->fail("Order not found.");
        if (!in_array($to, self::STATUSES, true)) return $this->fail("Invalid status.");

        $from = $order['orderStatus'];
        if ($from === $to) return ['success' => true, 'errors' => []];
        if ($from === 'Cancelled') return $this->fail("Order $orderID is cancelled and can no longer be changed.");
        if (($order['paymentStatus'] ?? '') === 'Completed') {
            return $this->fail("Order $orderID already has a recorded payment. Cancel the payment on the Payments page first, then change the status.");
        }

        try {
            $this->data->begin();
            $this->data->setStatus($orderID, $to);
            if ($to === 'Cancelled') $this->data->cancelPendingPayment($orderID);
            $this->tables->syncStatuses();
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Could not change the status. Please try again.");
        }
        return ['success' => true, 'errors' => []];
    }

    /** 永久刪除（admin）：已付款或已有收據的訂單不能刪，請用取消 */
    public function delete(string $orderID): array {
        $order = $this->data->findOrder($orderID);
        if (!$order) return $this->fail("Order not found.");
        if ($order['orderStatus'] === 'Completed') {
            return $this->fail("A paid (Completed) order cannot be deleted.");
        }
        if ($this->data->countReceipts($orderID) > 0) {
            return $this->fail("This order has receipts on record, so it cannot be deleted. Cancel it instead.");
        }

        try {
            $this->data->begin();
            $this->data->deleteItems($orderID);
            $this->data->deletePayments($orderID);
            $this->data->deleteOrder($orderID);
            $this->tables->syncStatuses();
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Delete failed. Please try again.");
        }
        return ['success' => true, 'errors' => []];
    }

    private function fail(string $msg): array {
        return ['success' => false, 'errors' => [$msg]];
    }
}
