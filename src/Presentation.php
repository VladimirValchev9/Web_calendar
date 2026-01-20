<?php
class Presentation {
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

    public function submit(string $title, string $category, int $studentId, string $facultyNumber = ''): void {
        $stmt = $this->db->prepare(
            "INSERT INTO presentations (student_id, faculty_number, title, category)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$studentId, $facultyNumber, $title, $category]);
    }

    public function getPending(): array {
        $stmt = $this->db->query(
            "SELECT p.*, u.email
             FROM presentations p
             JOIN users u ON u.id = p.student_id
             WHERE p.approved = 0"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApproved(): array {
        $stmt = $this->db->query(
            "SELECT p.id, p.title, p.category, u.email, s.date, s.time
             FROM presentations p
             JOIN users u ON u.id = p.student_id
             LEFT JOIN slots s ON s.user_id = p.student_id
             WHERE p.approved = 1
             GROUP BY p.id
             ORDER BY s.date, s.time"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedForStudent(int $studentId): array {
        $stmt = $this->db->prepare(
            "SELECT id, title, category
             FROM presentations
             WHERE approved = 1 AND student_id = ?"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve(int $id): void {
        $stmt = $this->db->prepare(
            "UPDATE presentations SET approved = 1 WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public function cancel(int $presentationId, int $studentId): void {
        $stmt = $this->db->prepare(
            "UPDATE slots SET user_id = NULL WHERE user_id = ?"
        );
        $stmt->execute([$studentId]);

        $stmt = $this->db->prepare(
            "DELETE FROM presentations WHERE id = ? AND student_id = ?"
        );
        $stmt->execute([$presentationId, $studentId]);
    }

    public function cleanupCancelled(): void {
        $this->db->query("
            DELETE FROM presentations
            WHERE student_id IS NULL
        ");
    }

    public function getApprovedForRadar(): array {
        $stmt = $this->db->query("
            SELECT p.id, p.title, p.category, u.email
            FROM presentations p
            JOIN users u ON u.id = p.student_id
            WHERE p.approved = 1
            AND p.student_id IS NOT NULL
            ORDER BY p.category, p.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}