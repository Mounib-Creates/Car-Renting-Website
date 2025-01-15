<?php

class UserStorage {
    private $filePath;

    public function __construct($filePath = 'users.json') {
        $this->filePath = $filePath;
    }

    public function loadUsers() {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?: [];
    }

    public function saveUser($userData) {
        $users = $this->loadUsers();

        foreach ($users as $user) {
            if ($user['name'] === $userData['name'] || $user['email'] === $userData['email']) {
                throw new Exception("Name or email already exists.");
            }
        }

        if (!isset($userData['isAdmin'])) {
            $userData['isAdmin'] = false;
        }
        $users[] = $userData;
        file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function verifyUser($email, $password) {
        $users = $this->loadUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                return $user;
            }
        }
        return null;
    }
}
