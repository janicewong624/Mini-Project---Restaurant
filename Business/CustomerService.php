<?php
require_once __DIR__ . '/../Data/UserData.php';

class CustomerService {
    private UserData $data;

    public function __construct(?UserData $data = null) {
        $this->data = $data ?? new UserData();
    }

    public function getAll(): array {
        return $this->data->getCustomers();
    }

    public function getById(string $id): ?array {
        if (!ctype_digit($id) || (int)$id < 1) return null;
        return $this->data->findCustomer($id);
    }

    public function add(
        string $name,
        string $email,
        string $contactNumber,
        string $password,
        string $confirmPassword
    ): array {
        [$errors, $name, $email, $contactNumber] = $this->validate($name, $email, $contactNumber);

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/', $password)) {
            $errors[] = 'Password must be at least 8 characters and contain letters and numbers.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        if ($errors) return ['success' => false, 'errors' => $errors];

        $id = $this->data->createCustomer(
            $name,
            $email,
            $contactNumber,
            password_hash($password, PASSWORD_DEFAULT)
        );
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    public function update(string $id, string $name, string $email, string $contactNumber): array {
        if ($this->getById($id) === null) return $this->fail('Customer not found.');

        [$errors, $name, $email, $contactNumber] = $this->validate($name, $email, $contactNumber, $id);
        if ($errors) return ['success' => false, 'errors' => $errors];

        $this->data->updateCustomer($id, $name, $email, $contactNumber);
        return ['success' => true, 'errors' => []];
    }

    public function delete(string $id): array {
        if ($this->getById($id) === null) return $this->fail('Customer not found.');

        try {
            if (!$this->data->deleteCustomer($id)) return $this->fail('Customer not found.');
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                return $this->fail('This customer has linked records and cannot be deleted.');
            }
            throw $error;
        }

        return ['success' => true, 'errors' => []];
    }

    private function validate(
        string $name,
        string $email,
        string $contactNumber,
        ?string $excludeEmailId = null
    ): array {
        $name = trim($name);
        $email = trim($email);
        $contactNumber = preg_replace('/[\s-]/', '', trim($contactNumber)) ?? '';
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors[] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Please enter a valid email address (maximum 150 characters).';
        } elseif ($this->data->emailExists($email, $excludeEmailId)) {
            $errors[] = 'This email address is already in use.';
        }
        if (!preg_match('/^\+?[0-9]{9,15}$/', $contactNumber)) {
            $errors[] = 'Please enter a valid phone number.';
        }

        return [$errors, $name, $email, $contactNumber];
    }

    private function fail(string $message): array {
        return ['success' => false, 'errors' => [$message]];
    }
}