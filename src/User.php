<?php
class User {

    private PDO $db;

    public function __construct() {
        $config = include __DIR__ . '/../config/config.php';
        $this->db = new PDO(
            "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8mb4",
            $config->DB_USER,
            $config->DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function createUser(string $email, string $password, string $role = 'student'): bool {
        $check = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (email, password, role) VALUES (?, ?, ?)"
        );

        return $stmt->execute([$email, $hash, $role]);
    }

    public function login($email, $password): bool {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function getUserInterests($user_id): array {
        $stmt = $this->db->prepare("SELECT interests FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? json_decode($data['interests'], true) : [];
    }

    public function setUserInterests($user_id, array $interests): bool {
        $stmt = $this->db->prepare("UPDATE users SET interests = ? WHERE id = ?");
        return $stmt->execute([json_encode($interests), $user_id]);
    }
}