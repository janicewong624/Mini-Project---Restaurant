<?php
require_once __DIR__ . '/../data/ReceiptData.php';
require_once __DIR__ . '/MailService.php';

class ReceiptService {
    private ReceiptData $data;

    public function __construct(?PDO $pdo = null) {
        $this->data = new ReceiptData($pdo);
    }

    /** 自動產生 Original（顧客）和 Duplicate（餐廳）兩張收據 */
    public function generate(string $paymentID): void {
        foreach (['Original', 'Duplicate'] as $type) {
            if (!$this->data->exists($paymentID, $type)) {
                $this->data->insert($paymentID, $type);
            }
        }
    }

    public function view(string $paymentID, string $type): ?array {
        if (!in_array($type, ['Original', 'Duplicate'], true)) $type = 'Original';

        $receipt = $this->data->findByPayment($paymentID, $type);
        $header  = $this->data->getHeader($paymentID);
        if (!$receipt || !$header) return null;

        return [
            'receipt' => $receipt,
            'header'  => $header,
            'items'   => $this->data->getItems($header['orderID']),
            'type'    => $type,
        ];
    }

    /** 把顧客版收據（Original）寄到顧客 email */
    public function emailToCustomer(string $paymentID): array {
        $data = $this->view($paymentID, 'Original');
        if (!$data) return ['success' => false, 'error' => 'Receipt not found.'];

        $h = $data['header'];
        if ($h['paymentStatus'] !== 'Completed') {
            return ['success' => false, 'error' => 'Only completed payments can be emailed.'];
        }

        $restaurantName = 'DineNest Restaurant';   // 與 header.php 的 $restaurantName 保持一致

        $method = $h['paymentMethod'] . (!empty($h['onlineType']) ? ' (' . $h['onlineType'] . ')' : '');

        $rows = '';
        foreach ($data['items'] as $it) {
            $rows .= '<tr><td>' . htmlspecialchars($it['foodName']) . '</td>'
                   . '<td align="center">x' . (int)$it['quantity'] . '</td>'
                   . '<td align="right">RM ' . number_format((float)$it['subtotal'], 2) . '</td></tr>';
        }

        $html = '<div style="font-family:Arial,sans-serif;max-width:420px;margin:auto;border:1px solid #ddd;padding:20px;">'
              . '<h2 style="text-align:center;margin:0;">' . htmlspecialchars($restaurantName) . '</h2>'
              . '<p style="text-align:center;color:#777;margin:4px 0 14px;">Payment Receipt</p><hr>'
              . '<p>Receipt No: ' . htmlspecialchars($data['receipt']['receiptID']) . '<br>'
              . 'Order: ' . htmlspecialchars($h['orderID']) . '<br>'
              . 'Date: ' . date('d/m/Y H:i', strtotime($h['paymentDate'])) . '<br>'
              . 'Table: ' . htmlspecialchars((string)$h['tableNumber']) . '</p><hr>'
              . '<table width="100%" cellpadding="4">' . $rows . '</table><hr>'
              . '<p style="font-size:18px;"><strong>Total: RM ' . number_format((float)$h['paymentAmount'], 2) . '</strong></p>'
              . '<p>Payment Method: ' . htmlspecialchars($method) . '</p>'
              . '<p style="text-align:center;color:#777;">Thank you for dining with us!</p></div>';

        return (new MailService())->send(
            $h['customerEmail'], $h['customerName'],
            'Your receipt - Order ' . $h['orderID'], $html
        );
    }
}