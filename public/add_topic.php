<!-- <?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die("Нямате достъп до тази страница.");
}

$config = require __DIR__ . '/../config/config.php';

$pdo = new PDO(
    "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8",
    $config->DB_USER,
    $config->DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];

    $interests = [
        'frontend' => intval($_POST['frontend'] ?? 0),
        'backend' => intval($_POST['backend'] ?? 0),
        'basics' => intval($_POST['basics'] ?? 0),
        'technologies' => intval($_POST['technologies'] ?? 0)
    ];

    if ($title && $category) {
        $stmt = $pdo->prepare("
            INSERT INTO topics (title, category, interests, approved)
            VALUES (?, ?, ?, 1)
        ");

        try {
            $stmt->execute([
                $title,
                $category,
                json_encode($interests)
            ]);
            $msg = "Темата е добавена успешно!";
        } catch (PDOException $e) {
            $error = "Тази тема вече съществува.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Добавяне на тема</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<h1>Добавяне на нова тема</h1>

<?php if ($msg): ?>
    <p style="color:green"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" class="topic-form">

    <label>
        Заглавие на темата
        <input type="text" name="title" required>
    </label>

    <label>
        Категория
        <select name="category" required>
            <option value="">-- Избери --</option>
            <option value="frontend">Frontend</option>
            <option value="backend">Backend</option>
            <option value="basics">Basics</option>
            <option value="technologies">Technologies</option>
        </select>
    </label>

    <h3>Интереси (0–5)</h3>

    <?php
    $fields = ['frontend', 'backend', 'basics', 'technologies'];
    foreach ($fields as $f):
        $val = $_POST[$f] ?? 0;
    ?>
        <label>
            <?= ucfirst($f) ?>
            <input type="number" name="<?= $f ?>" min="0" max="5" value="<?= $val ?>">
        </label>

        <div style="width:200px;height:10px;background:#eee;margin-bottom:10px;">
            <div style="
                width:<?= ($val / 5) * 100 ?>%;
                height:100%;
                background:#3498db;">
            </div>
        </div>
    <?php endforeach; ?>

    <button type="submit">Добави тема</button>
</form>

</body>
</html> -->