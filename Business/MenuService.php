<?php
require_once __DIR__ . '/../Data/MenuData.php';

class MenuService {
    private MenuData $data;

    private const CATEGORY_OPTIONS = [
        'Bubble Milk Tea',
        'Noodle Soups',
        'Spaghetti',
        'Special Items',
        'Nyonya Series',
        'Beverages',
        'Western Food',
        'Rice',
    ];

    public function __construct(?MenuData $data = null) {
        $this->data = $data ?? new MenuData();
    }

    public static function categories(): array {
        return self::CATEGORY_OPTIONS;
    }

    public function getAll(): array {
        return $this->data->findAll();
    }

    public function getPopular(int $limit = 4): array {
        return $this->data->findPopular($limit);
    }

    public function getById(string $id): ?array {
        return $id !== '' ? $this->data->findById($id) : null;
    }

    /** @return array{success:bool,errors?:string[],id?:string,syncWarning?:string} */
    public function save(?string $id, array $input, array $upload): array {
        $foodName = trim($this->stringValue($input['foodName'] ?? null));
        $description = trim($this->stringValue($input['description'] ?? null));
        $category = trim($this->stringValue($input['category'] ?? null));
        $priceValue = trim($this->stringValue($input['price'] ?? null));
        $isPopular = ($input['isPopular'] ?? '') === '1';
        $removeImage = ($input['remove_image'] ?? '') === '1';
        $errors = [];

        if ($foodName === '' || mb_strlen($foodName) > 100) {
            $errors[] = 'Please enter a dish name (up to 100 characters).';
        }
        if (mb_strlen($description) > 500) {
            $errors[] = 'Description must be 500 characters or fewer.';
        }
        if ($category === '' || mb_strlen($category) > 80) {
            $errors[] = 'Please enter a category (up to 80 characters).';
        }
        if (!is_numeric($priceValue) || (float)$priceValue <= 0 || (float)$priceValue > 99999.99) {
            $errors[] = 'Please enter a valid price greater than RM 0.00.';
        }

        $existing = $id !== null ? $this->data->findById($id) : null;
        if ($id !== null && $existing === null) {
            $errors[] = 'This menu item could not be found.';
        }

        $image = (string)($existing['image'] ?? '');
        if ($removeImage) {
            $image = '';
        }

        $newImage = '';
        if (!$errors) {
            $newImage = $this->storeImage($upload, $errors);
            if ($newImage !== '') {
                $image = $newImage;
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            if ($id === null) {
                $savedId = $this->data->create(
                    $foodName,
                    $description,
                    (float)$priceValue,
                    $category,
                    $image,
                    $isPopular
                );
            } else {
                $this->data->update(
                    $id,
                    $foodName,
                    $description,
                    (float)$priceValue,
                    $category,
                    $image,
                    $isPopular
                );
                $savedId = $id;
            }
        } catch (PDOException $error) {
            if ($newImage !== '') {
                $this->removeStoredImage($newImage);
            }
            throw $error;
        }

        $syncWarning = '';
        if (!$this->data->recordSetupUpsert(
            $savedId,
            $foodName,
            $description,
            (float)$priceValue,
            $category,
            $image,
            $isPopular
        )) {
            $syncWarning = 'The menu was saved, but Database/menu_setup.sql could not be updated. '
                . 'Check that the file is writable before committing to GitHub.';
        }

        if ($existing !== null && $image !== (string)($existing['image'] ?? '')) {
            $this->removeStoredImage((string)($existing['image'] ?? ''));
        }

        return ['success' => true, 'id' => $savedId, 'syncWarning' => $syncWarning];
    }

    /** @return array{success:bool,errors?:string[],syncWarning?:string} */
    public function delete(string $id): array {
        $item = $this->data->findById($id);
        if ($item === null) {
            return ['success' => false, 'errors' => ['This menu item could not be found.']];
        }
        if ($this->data->countOrderItems($id) > 0) {
            return [
                'success' => false,
                'errors' => ['This item is part of order history and cannot be deleted.'],
            ];
        }

        $this->data->delete($id);
        $syncWarning = '';
        if (!$this->data->recordSetupDelete($id)) {
            $syncWarning = 'The menu item was deleted, but Database/menu_setup.sql could not be updated. '
                . 'Check that the file is writable before committing to GitHub.';
        }
        $this->removeStoredImage((string)($item['image'] ?? ''));
        return ['success' => true, 'syncWarning' => $syncWarning];
    }

    private function stringValue($value): string {
        return is_string($value) ? $value : '';
    }

    private function storeImage(array $upload, array &$errors): string {
        $errorCode = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if (!is_int($errorCode)) {
            $errors[] = 'The uploaded photo could not be processed.';
            return '';
        }
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            return '';
        }
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = $errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE
                ? 'Photo must be 5 MB or smaller.'
                : 'The photo upload failed. Please try again.';
            return '';
        }

        $temporaryPath = $upload['tmp_name'] ?? '';
        $fileSize = $upload['size'] ?? 0;
        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            $errors[] = 'The uploaded photo is invalid.';
            return '';
        }
        if (!is_int($fileSize) || $fileSize < 1 || $fileSize > 5 * 1024 * 1024) {
            $errors[] = 'Photo must be 5 MB or smaller.';
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            $errors[] = 'Photo validation is unavailable on the server.';
            return '';
        }
        $mimeType = finfo_file($finfo, $temporaryPath);
        finfo_close($finfo);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
            $errors[] = 'Please upload a JPG, PNG, or WebP photo.';
            return '';
        }

        $directory = __DIR__ . '/../Assets/images/menu';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            $errors[] = 'The menu photo folder could not be created.';
            return '';
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        if (!move_uploaded_file($temporaryPath, $directory . '/' . $filename)) {
            $errors[] = 'The photo could not be saved. Please try again.';
            return '';
        }

        return $filename;
    }

    private function removeStoredImage(string $filename): void {
        $filename = basename(str_replace('\\', '/', $filename));
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return;
        }

        $path = __DIR__ . '/../Assets/images/menu/' . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log('Unable to remove old menu image: ' . $path);
        }
    }
}
