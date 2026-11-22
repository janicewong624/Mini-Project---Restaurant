<?php
require_once __DIR__ . '/../Data/TableData.php';

class TableService {
    // 桌子狀態。Available / Occupied 會依訂單自動更新；Reserved 由 admin 手動設定。
    const STATUSES = ['Available', 'Occupied', 'Reserved'];

    private TableData $data;

    public function __construct(?PDO $pdo = null) {
        $this->data = new TableData($pdo);
    }

    public function getAll(): array {
        $this->data->syncStatuses();
        return $this->data->findAll();
    }

    public function getById(string $id): ?array {
        $this->data->syncStatuses();
        return $this->data->findById($id);
    }

    /** 驗證桌號、座位數、狀態，回傳錯誤訊息陣列（空陣列 = 通過） */
    private function validate(string $number, string $capacity, string $status, ?string $excludeID): array {
        $errors = [];

        if ($number === '') {
            $errors[] = "Please enter a table number.";
        } elseif (!preg_match('/^[A-Za-z0-9-]{1,10}$/', $number)) {
            $errors[] = "Table number may only contain letters, numbers and '-' (max 10 characters).";
        } elseif ($this->data->findByNumber($number, $excludeID) !== null) {
            $errors[] = "Table number $number already exists.";
        }

        if ($capacity === '') {
            $errors[] = "Please enter the seating capacity.";
        } elseif (!ctype_digit($capacity)) {
            $errors[] = "Invalid data, seating capacity must be a whole number.";
        } elseif ((int)$capacity < 1 || (int)$capacity > 20) {
            $errors[] = "Seating capacity should be between 1 and 20.";
        }

        if ($status === '') {
            $errors[] = "Please select a table status.";
        } elseif (!in_array($status, self::STATUSES, true)) {
            $errors[] = "Invalid table status.";
        }
        return $errors;
    }

    public function add(string $number, string $capacity, string $status): array {
        $number = trim($number);
        $capacity = trim($capacity);
        $errors = $this->validate($number, $capacity, $status, null);
        if ($errors) return ['success' => false, 'errors' => $errors];

        $id = $this->data->insert($number, (int)$capacity, $status);
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    public function update(string $id, string $number, string $capacity, string $status): array {
        $number = trim($number);
        $capacity = trim($capacity);
        if ($this->data->findById($id) === null) return $this->fail("Table not found.");

        $errors = $this->validate($number, $capacity, $status, $id);
        if ($errors) return ['success' => false, 'errors' => $errors];

        $this->data->update($id, $number, (int)$capacity, $status);
        return ['success' => true, 'errors' => []];
    }

    public function delete(string $id): array {
        $table = $this->data->findById($id);
        if ($table === null) return $this->fail("Table not found.");
        if ($this->data->countOrders($id) > 0) {
            return $this->fail("Table {$table['tableNumber']} has order records, so it cannot be deleted. "
                             . "You can set its status to Reserved to stop customers from using it.");
        }
        $this->data->delete($id);
        return ['success' => true, 'errors' => []];
    }

    private function fail(string $msg): array {
        return ['success' => false, 'errors' => [$msg]];
    }
}
