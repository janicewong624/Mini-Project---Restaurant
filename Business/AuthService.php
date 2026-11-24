<?php
require_once __DIR__ . '/../Data/UserData.php';

class AuthService {
    private UserData $users;

    public function __construct(?UserData $users = null) {
        $this->users = $users ?? new UserData();
    }

    /** @return array{success:bool,error?:string,userID?:string} */
    public function register(
        string $name,
        string $email,
        string $contactNumber,
        string $password,
        string $confirmPassword,
        bool $acceptedTerms
    ): array {
        $name = trim($name);
        $email = trim($email);
        $contactNumber = preg_replace('/[\s-]/', '', trim($contactNumber)) ?? '';

        if ($name === '' || $email === '' || $contactNumber === '' || $password === '' || $confirmPassword === '') {
            return ['success' => false, 'error' => 'empty_fields'];
        }
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            return ['success' => false, 'error' => 'invalid_name'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            return ['success' => false, 'error' => 'invalid_email'];
        }
        if (!preg_match('/^\+?[0-9]{9,15}$/', $contactNumber)) {
            return ['success' => false, 'error' => 'invalid_phone'];
        }
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/', $password)) {
            return ['success' => false, 'error' => 'weak_password'];
        }
        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'password_mismatch'];
        }
        if (!$acceptedTerms) {
            return ['success' => false, 'error' => 'terms_required'];
        }
        if ($this->users->emailExists($email)) {
            return ['success' => false, 'error' => 'email_exists'];
        }

        $userID = $this->users->createCustomer(
            $name,
            $email,
            $contactNumber,
            password_hash($password, PASSWORD_DEFAULT)
        );

        return ['success' => true, 'userID' => $userID];
    }

    public function authenticate(string $email, string $password): ?array {
        if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL) || $password === '') {
            return null;
        }

        $user = $this->users->findByEmail(trim($email));
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        unset($user['password']);
        return $user;
    }

    /** @return array{success:bool,error?:string,user?:array} */
    public function authenticateWithResult(string $email, string $password): array {
        $email = trim($email);

        if ($email === '' || $password === '') {
            return ['success' => false, 'error' => 'empty_fields'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'invalid_email'];
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'email_not_found'];
        }
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'wrong_password'];
        }

        unset($user['password']);
        return ['success' => true, 'user' => $user];
    }
}