<?php
require_once __DIR__ . '/Database.php';

class MenuData {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::connect();
    }

    public function findAll(): array {
        return $this->pdo->query(
            "SELECT menuItemID, foodName, description, price, category, image, isPopular
             FROM menuitem ORDER BY category, foodName"
        )->fetchAll();
    }

    public function findPopular(int $limit = 4): array {
        $stmt = $this->pdo->prepare(
            "SELECT menuItemID, foodName, description, price, category, image
             FROM menuitem WHERE isPopular = 1 ORDER BY foodName LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT menuItemID, foodName, description, price, category, image, isPopular
             FROM menuitem WHERE menuItemID = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(
        string $foodName,
        string $description,
        float $price,
        string $category,
        string $image,
        bool $isPopular
    ): string {
        $id = Database::generateID($this->pdo, 'menuitem', 'menuItemID', 'M', 4);
        $stmt = $this->pdo->prepare(
            "INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $foodName, $description, $price, $category, $image, (int)$isPopular]);
        return $id;
    }

    public function recordSetupUpsert(
        string $id,
        string $foodName,
        string $description,
        float $price,
        string $category,
        string $image,
        bool $isPopular
    ): bool {
        $quotedId = $this->pdo->quote($id);
        $quotedName = $this->pdo->quote($foodName);
        $quotedDescription = $this->pdo->quote($description);
        $quotedCategory = $this->pdo->quote($category);
        $quotedImage = $this->pdo->quote($image);

        if (
            $quotedId === false
            || $quotedName === false
            || $quotedDescription === false
            || $quotedCategory === false
            || $quotedImage === false
        ) {
            error_log('Unable to quote menu item values for Database/menu_setup.sql.');
            return false;
        }

        $priceSql = number_format($price, 2, '.', '');
        $popularSql = $isPopular ? '1' : '0';
        $statement = "INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)\n"
            . "VALUES ($quotedId, $quotedName, $quotedDescription, $priceSql, $quotedCategory, $quotedImage, $popularSql)\n"
            . "ON DUPLICATE KEY UPDATE foodName = $quotedName, description = $quotedDescription, "
            . "price = $priceSql, category = $quotedCategory, image = $quotedImage, isPopular = $popularSql;";

        return $this->appendSetupChange("\n-- Menu item added or updated\n$statement\n");
    }

    public function recordSetupDelete(string $id): bool {
        $quotedId = $this->pdo->quote($id);
        if ($quotedId === false) {
            error_log('Unable to quote menu item ID for Database/menu_setup.sql.');
            return false;
        }

        return $this->appendSetupChange(
            "\n-- Menu item deleted\nDELETE FROM menuitem WHERE menuItemID = $quotedId;\n"
        );
    }

    private function appendSetupChange(string $change): bool {
        $path = __DIR__ . '/../Database/menu_setup.sql';
        if (!is_file($path) || !is_writable($path)) {
            error_log('Database/menu_setup.sql is missing or not writable.');
            return false;
        }

        $written = @file_put_contents($path, $change, FILE_APPEND | LOCK_EX);
        if ($written !== strlen($change)) {
            $error = error_get_last();
            error_log(
                'Unable to append menu change to Database/menu_setup.sql.'
                . (is_array($error) && isset($error['message']) ? ' ' . $error['message'] : '')
            );
            return false;
        }

        return true;
    }

    public function update(
        string $id,
        string $foodName,
        string $description,
        float $price,
        string $category,
        string $image,
        bool $isPopular
    ): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE menuitem
             SET foodName = ?, description = ?, price = ?, category = ?, image = ?, isPopular = ?
             WHERE menuItemID = ?"
        );
        return $stmt->execute([
            $foodName,
            $description,
            $price,
            $category,
            $image,
            (int)$isPopular,
            $id,
        ]);
    }

    public function countOrderItems(string $id): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orderitem WHERE menuItemID = ?");
            $stmt->execute([$id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $error) {
            if ($error->getCode() === '42S02') {
                return 0;
            }
            throw $error;
        }
    }

    public function delete(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM menuitem WHERE menuItemID = ?");
        return $stmt->execute([$id]);
    }
}
