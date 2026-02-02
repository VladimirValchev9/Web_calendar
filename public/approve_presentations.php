<?php
session_start();
require_once __DIR__ . '/../src/Presentation.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

$presentation = new Presentation();

if (isset($_GET['approve'])) {
    $presentation->approve($_GET['approve']);
    header('Location: approve_presentations.php');
    exit;
}

$list = $presentation->getPending();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<title>Одобрение на презентации</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>Чакащи одобрение теми</h1>

    <?php if (empty($list)): ?>
        <p class="hint">Няма чакащи одобрение теми в момента.</p>
    <?php else: ?>
        <table class="calendar-table">
        <tr>
            <th>Факултетен номер</th>
            <th>Тема</th>
            <th>Студент</th>
            <th>Категория</th>
            <th>Действие</th>
        </tr>

        <?php foreach ($list as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['faculty_number']) ?></td>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td>
                <span class="badge"><?= htmlspecialchars($p['category']) ?></span>
            </td>
            <td>
                <a href="?approve=<?= $p['id'] ?>" class="approve-link">
                    <button>Одобри</button>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>