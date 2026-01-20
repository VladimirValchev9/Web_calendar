<?php
session_start();
require_once __DIR__ . '/../src/Presentation.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

include __DIR__ . '/header.php';

$presentation = new Presentation();

if (isset($_GET['approve'])) {
    $presentation->approve($_GET['approve']);
}

$list = $presentation->getPending();
?>

<h1>Чакащи одобрение теми</h1>

<table border="1">
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
    <td><?= $p['category'] ?></td>
    <td>
        <a href="?approve=<?= $p['id'] ?>">Одобри</a>
    </td>
</tr>
<?php endforeach; ?>
</table>