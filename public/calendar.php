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

$config = require __DIR__ . '/../config/config.php';
$db = new PDO(
    "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8mb4",
    $config->DB_USER,
    $config->DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$msg = '';

if (isset($_POST['save_timer'])) {
    $slotId = (int)$_POST['slot_id'];
    $minutes = (int)$_POST['minutes'];
    $seconds = (int)$_POST['seconds'];
    
    $stmt = $db->prepare("INSERT INTO presentation_times (slot_id, duration_minutes, duration_seconds) VALUES (?, ?, ?)");
    $stmt->execute([$slotId, $minutes, $seconds]);
    $msg = "Времето е записано успешно!";
}

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
<style>
.timer-controls {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.timer-display {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    min-width: 80px;
}

.timer-display.running {
    color: #27ae60;
}

.timer-display.stopped {
    color: #e74c3c;
}

.timer-btn {
    padding: 5px 12px;
    font-size: 13px;
    cursor: pointer;
}

.duration-input {
    width: 60px;
    padding: 4px;
    margin-right: 5px;
}

.saved-time {
    color: #7f8c8d;
    font-style: italic;
    font-size: 14px;
}
</style>
</head>
<body>

<h1>Календар с презентации</h1>

<?php if (!empty($msg)) echo "<p style='color:red;'>$msg</p>"; ?>

<table class="calendar-table">
<tr>
    <th>Дата</th>
    <th>Час</th>
    <th>Тема</th>
    <th>Статус</th>
    <th>Действие / Таймер</th>
</tr>

<?php foreach ($slots as $slot):

    $status_text = 'Свободен';
    $status_class = 'slot-free';
    $presTitle = '';
    $isApproved = false;

    if ($slot['user_id']) {
        $stmt = $db->prepare("SELECT approved, title FROM presentations WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$slot['user_id']]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pres) {
            $presTitle = $pres['title'];
            if ($pres['approved']) {
                $status_text = 'Зает';
                $status_class = 'slot-booked';
                $isApproved = true;
            } else {
                $status_text = 'Чака';
                $status_class = 'slot-pending';
            }
        }
    }
    
    $stmtTimer = $db->prepare("SELECT duration_minutes, duration_seconds FROM presentation_times WHERE slot_id = ? ORDER BY recorded_at DESC LIMIT 1");
    $stmtTimer->execute([$slot['id']]);
    $savedTimer = $stmtTimer->fetch(PDO::FETCH_ASSOC);
?>

<tr class="<?= $status_class ?>">
    <td><?= htmlspecialchars($slot['date']) ?></td>
    <td><?= htmlspecialchars($slot['time']) ?></td>
    <td><?= htmlspecialchars($presTitle) ?></td>
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
        <?php elseif ($slot['user_id'] == $_SESSION['user_id'] && !$isApproved): ?>
            <form method="post">
                <input type="hidden" name="cancel_slot_id" value="<?= $slot['id'] ?>">
                <button type="submit" name="cancel_slot">Отмени</button>
            </form>
        <?php elseif ($isApproved): ?>
            <?php if ($savedTimer): ?>
                <div class="saved-time">
                    ⏱ Записано време: <?= $savedTimer['duration_minutes'] ?>:<?= str_pad($savedTimer['duration_seconds'], 2, '0', STR_PAD_LEFT) ?>
                </div>
            <?php else: ?>
                <div class="timer-controls" id="timer-control-<?= $slot['id'] ?>">
                    <input type="number" class="duration-input" id="dur-<?= $slot['id'] ?>" min="1" max="60" value="6" placeholder="мин">
                    <button class="timer-btn" onclick="startTimer(<?= $slot['id'] ?>)">▶ Старт</button>
                    <button class="timer-btn" onclick="stopTimer(<?= $slot['id'] ?>)" style="display:none;">⏸ Стоп</button>
                    <div class="timer-display" id="display-<?= $slot['id'] ?>">--:--</div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>
</table>

<script>
const timers = {};

function startTimer(slotId) {
    const durationInput = document.getElementById('dur-' + slotId);
    const minutes = parseInt(durationInput.value) || 6;
    
    const control = document.getElementById('timer-control-' + slotId);
    const startBtn = control.querySelector('button:nth-of-type(1)');
    const stopBtn = control.querySelector('button:nth-of-type(2)');
    const display = document.getElementById('display-' + slotId);
    
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    durationInput.disabled = true;
    
    let totalSeconds = minutes * 60;
    let elapsed = 0;
    
    display.classList.add('running');
    display.classList.remove('stopped');
    
    updateDisplay(slotId, elapsed);
    
    timers[slotId] = {
        interval: setInterval(() => {
            elapsed++;
            updateDisplay(slotId, elapsed);
        }, 1000),
        elapsed: elapsed,
        startBtn: startBtn,
        stopBtn: stopBtn,
        display: display,
        durationInput: durationInput
    };
}

function stopTimer(slotId) {
    if (!timers[slotId]) return;
    
    clearInterval(timers[slotId].interval);
    
    const elapsed = timers[slotId].elapsed;
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    
    timers[slotId].display.classList.remove('running');
    timers[slotId].display.classList.add('stopped');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="save_timer" value="1">
        <input type="hidden" name="slot_id" value="${slotId}">
        <input type="hidden" name="minutes" value="${minutes}">
        <input type="hidden" name="seconds" value="${seconds}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function updateDisplay(slotId, elapsed) {
    if (!timers[slotId]) return;
    
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    
    const display = document.getElementById('display-' + slotId);
    display.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    
    timers[slotId].elapsed = elapsed;
}
</script>

</body>
</html>