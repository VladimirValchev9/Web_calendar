<?php
class Calendar {
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

    public function getSlots(): array {
        return $this->db
            ->query("SELECT * FROM slots ORDER BY date, time")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function bookSlot(int $userId, int $slotId): bool {
        $stmt = $this->db->prepare(
            "UPDATE slots SET user_id = ? WHERE id = ? AND user_id IS NULL"
        );
        return $stmt->execute([$userId, $slotId]);
    }

    public function cancelSlotByUser(int $userId, int $slotId): bool {
        $stmt = $this->db->prepare(
            "UPDATE slots SET user_id = NULL WHERE id = ? AND user_id = ?"
        );
        $ok = $stmt->execute([$slotId, $userId]);

        if ($ok) {
            $stmt2 = $this->db->prepare(
                "DELETE FROM presentations WHERE student_id = ? AND approved = 0"
            );
            $stmt2->execute([$userId]);
        }

        return $ok;
    }

    public function swapSlots(int $slotId1, int $slotId2): bool {
        $stmt = $this->db->prepare("SELECT id, user_id FROM slots WHERE id IN (?, ?)");
        $stmt->execute([$slotId1, $slotId2]);
        $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($slots) < 2 || $slots[0]['user_id'] === null || $slots[1]['user_id'] === null) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE slots SET user_id = ? WHERE id = ?");
        $stmt->execute([$slots[1]['user_id'], $slots[0]['id']]);
        $stmt->execute([$slots[0]['user_id'], $slots[1]['id']]);
        return true;
    }

    public function autoAssignPresentations(array $presentations): void {
        $slots = $this->getSlots();

        foreach ($presentations as $p) {
            foreach ($slots as $slot) {
                if ($slot['user_id'] === null && !$this->hasConflict($p['category'], $slot['date'])) {
                    $this->bookSlot($p['student_id'], $slot['id']);
                    break;
                }
            }
        }
    }

    private function hasConflict(string $category, string $date): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM slots s
             JOIN presentations p ON s.user_id = p.student_id
             WHERE s.date = ? AND p.category = ? AND s.user_id IS NOT NULL"
        );
        $stmt->execute([$date, $category]);
        return $stmt->fetchColumn() > 0;
    }
}