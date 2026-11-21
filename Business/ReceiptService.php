<?php
require_once __DIR__ . '/../data/ReceiptData.php';

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
}