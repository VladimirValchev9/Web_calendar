<?php
session_start();
require_once __DIR__ . '/../src/Calendar.php';
require_once __DIR__ . '/../src/Presentation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/header.php';

$calendar = new Calendar();
$presentation = new Presentation();
$db = new PDO(
    "mysql:host=127.0.0.1;dbname=web_calendar;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$msg = '';

if (isset($_POST['submit_slot'])) {
    $slotId = (int)$_POST['slot_id'];
    $facultyNumber = trim($_POST['faculty_number']);
    $topicId = (int)$_POST['topic_id'];

    $stmtTopic = $db->prepare("SELECT title, category FROM topics WHERE id = ?");
    $stmtTopic->execute([$topicId]);
    $topicData = $stmtTopic->fetch(PDO::FETCH_ASSOC);

    if ($topicData) {
        $title = $topicData['title'];
        $category = $topicData['category'];

        $stmtDate = $db->prepare("
            SELECT s.date, p.title 
            FROM slots s
            JOIN presentations p ON p.student_id = s.user_id
            WHERE s.date = (SELECT date FROM slots WHERE id = ?)
        ");
        $stmtDate->execute([$slotId]);
        $titlesOnDay = array_column($stmtDate->fetchAll(PDO::FETCH_ASSOC), 'title');

        $wordsNew = array_map('strtolower', explode(" ", $title));
        $conflict = false;
        foreach ($titlesOnDay as $existingTitle) {
            $wordsExisting = array_map('strtolower', explode(" ", $existingTitle));
            if (count(array_intersect($wordsNew, $wordsExisting)) > 0) {
                $conflict = true;
                break;
            }
        }

        if ($conflict) {
            $msg = "Вече има тема с подобни думи в този ден. Избери друг слот или тема.";
        } else {
            if ($calendar->bookSlot($_SESSION['user_id'], $slotId)) {
                $presentation->submit($title, $category, $_SESSION['user_id'], $facultyNumber);
                $msg = "Слотът е запазен успешно!";
            } else {
                $msg = "Този слот вече е зает.";
            }
        }
    } else {
        $msg = "Избраната тема не съществува.";
    }
}

if (isset($_POST['cancel_slot'])) {
    $slotId = (int)$_POST['cancel_slot_id'];
    $msg = $calendar->cancelSlotByUser($_SESSION['user_id'], $slotId)
        ? "Слотът е отменен и вече е свободен!"
        : "Грешка при отмяна на слота.";
}

$slots = $calendar->getSlots();
$stmtTopics = $db->prepare("SELECT id, title FROM topics WHERE approved = 1 ORDER BY title ASC");
$stmtTopics->execute();
$topics = $stmtTopics->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<title>Календар</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Календар с презентации</h1>

<?php if (!empty($msg)) echo "<p style='color:red;'>$msg</p>"; ?>

<table class="calendar-table">
<tr>
    <th>Дата</th>
    <th>Час</th>
    <th>Статус</th>
    <th>Действие</th>
</tr>

<?php foreach ($slots as $slot):

    $status_text = 'Свободен';
    $status_class = 'slot-free';

    if ($slot['user_id']) {
        $stmt = $db->prepare("SELECT approved, title FROM presentations WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$slot['user_id']]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pres) {
            if ($pres['approved']) {
                $status_text = 'Зает';
                $status_class = 'slot-booked';
            } else {
                $status_text = 'Чака';
                $status_class = 'slot-pending';
            }
        }
    }
?>

<tr class="<?= $status_class ?>">
    <td><?= htmlspecialchars($slot['date']) ?></td>
    <td><?= htmlspecialchars($slot['time']) ?></td>
    <td><?= $status_text ?></td>
    <td>
        <?php if (!$slot['user_id']): ?>
            <form method="post" class="slot-form">
                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                <input type="text" name="faculty_number" placeholder="Фак. №" required>
                <select name="topic_id" required>
                    <option value="">-- Избери тема --</option>
                    <?php foreach($topics as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="submit_slot">Запиши</button>
            </form>
        <?php elseif ($slot['user_id'] == $_SESSION['user_id']): ?>
            <form method="post">
                <input type="hidden" name="cancel_slot_id" value="<?= $slot['id'] ?>">
                <button type="submit" name="cancel_slot">Отмени</button>
            </form>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>
</table>

</body>
</html>