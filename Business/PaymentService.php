<?php
require_once __DIR__ . '/../data/PaymentData.php';
require_once __DIR__ . '/ReceiptService.php';

class PaymentService {
    const METHODS = ['Cash', 'Online Payment'];

    private PaymentData $data;

    public function __construct() {
        $this->data = new PaymentData();
    }

    public function getAll(): array         { return $this->data->findAll(); }
    public function getById(string $id): ?array { return $this->data->findById($id); }
    public function getPayableOrders(): array   { return $this->data->getPayableOrders(); }

    /** 新增付款（確認收款）：Completed + 訂單 Completed + 自動產生兩張收據 */
    public function add(string $orderID, string $method): array {
        $errors = [];
        $orderID = trim($orderID);

        if ($orderID === '')                         $errors[] = "Please select an order.";
        if ($method === '')                          $errors[] = "Please select a payment method.";
        elseif (!in_array($method, self::METHODS, true)) $errors[] = "Invalid payment method.";
        if ($errors) return ['success' => false, 'errors' => $errors];

        $order = $this->data->getOrder($orderID);
        if (!$order)                                  return $this->fail("Order not found.");
        if ($order['orderStatus'] === 'Cancelled')    return $this->fail("A cancelled order cannot be paid.");
        if ((float)$order['totalAmount'] <= 0)        return $this->fail("Order total must be greater than 0.");

        $existing = $this->data->findByOrder($orderID);
        if ($existing && $existing['paymentStatus'] === 'Completed') return $this->fail("This order has already been paid.");
        if ($existing && $existing['paymentStatus'] === 'Cancelled') return $this->fail("The payment for this order was cancelled.");

        $amount = (float)$order['totalAmount'];   // 金額一律用訂單總額，不接受手動輸入

        try {
            $this->data->begin();
            if ($existing) {                       // 訂單建立時已有 Pending 付款
                $paymentID = $existing['paymentID'];
                $this->data->completePending($paymentID, $amount, $method);
            } else {
                $paymentID = $this->data->insert($orderID, $amount, $method, 'Completed');
            }
            $this->data->setOrderStatus($orderID, 'Completed');
            (new ReceiptService($this->data->pdo()))->generate($paymentID);
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Payment failed, nothing was saved. Please try again.");
        }
        return ['success' => true, 'errors' => [], 'id' => $paymentID];
    }

    /** 修改付款方式（金額跟訂單綁定，不能改） */
    public function updateMethod(string $id, string $method): array {
        $payment = $this->data->findById($id);
        if (!$payment)                                   return $this->fail("Payment record not found.");
        if ($payment['paymentStatus'] === 'Cancelled')   return $this->fail("A cancelled payment cannot be edited.");
        if ($method === '')                              return $this->fail("Please select a payment method.");
        if (!in_array($method, self::METHODS, true))     return $this->fail("Invalid payment method.");

        $this->data->updateMethod($id, $method);
        return ['success' => true, 'errors' => []];
    }

    /** 取消付款：付款 Cancelled，訂單退回 Pending，收據保留但標示作廢 */
    public function cancel(string $id): array {
        $payment = $this->data->findById($id);
        if (!$payment)                                   return $this->fail("Payment record not found.");
        if ($payment['paymentStatus'] === 'Cancelled')   return $this->fail("This payment is already cancelled.");

        try {
            $this->data->begin();
            $this->data->setStatus($id, 'Cancelled');
            $this->data->setOrderStatus($payment['orderID'], 'Pending');
            $this->data->commit();
        } catch (Throwable $ex) {
            $this->data->rollBack();
            return $this->fail("Cancel failed. Please try again.");
        }
        return ['success' => true, 'errors' => []];
    }

    private function fail(string $msg): array {
        return ['success' => false, 'errors' => [$msg]];
    }
}