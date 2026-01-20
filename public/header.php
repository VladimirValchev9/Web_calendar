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
        <a href="approve_presentations.php" style="color:white;margin-right:15px;">Одобрение</a>
    <?php endif; ?>

    <a href="recommendation.php" style="color:white;margin-right:15px;">Препоръчани</a>
    <a href="radar.php" style="color:white;margin-right:15px;">Радар</a>
    <a href="timer.php" style="color:white;margin-right:15px;">Таймер</a>
    <a href="logout.php" style="color:white;">Изход</a>
</nav>
<hr>