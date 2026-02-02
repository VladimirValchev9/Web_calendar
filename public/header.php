<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentFile = basename($_SERVER['PHP_SELF']);

if (in_array($currentFile, ['index.php', 'register.php'])) {
    return;
}

if (!isset($_SESSION['user_id'])) {
    return;
}
?>

<nav style="background:#2980b9;padding:10px;">
    <a href="calendar.php" style="color:white;margin-right:15px;">Календар</a>

    <?php if ($_SESSION['role'] === 'teacher'): ?>
        <a href="add_user.php" style="color:white;margin-right:15px;">Добави потребител</a>
        <a href="import_students.php" style="color:white;margin-right:15px;">Импорт студенти</a>
        <a href="export_students.php" style="color:white;margin-right:15px;">Експорт студенти</a>
        <a href="import_topics.php" style="color:white;margin-right:15px;">Импорт теми</a>
        <a href="export_topics.php" style="color:white;margin-right:15px;">Експорт теми</a>
        <a href="generate_slots.php" style="color:white;margin-right:15px;">Генератор слотове</a>
        <a href="import_slots.php" style="color:white;margin-right:15px;">Импорт слотове</a>
        <a href="approve_presentations.php" style="color:white;margin-right:15px;">Одобрение</a>
    <?php endif; ?>

    <a href="recommendation.php" style="color:white;margin-right:15px;">Препоръчани</a>
    <a href="radar.php" style="color:white;margin-right:15px;">Радар</a>
    <a href="timer.php" style="color:white;margin-right:15px;">Таймер</a>
    <a href="logout.php" style="color:white;">Изход</a>
</nav>
<hr>