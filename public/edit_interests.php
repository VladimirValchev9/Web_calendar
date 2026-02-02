<?php
session_start();
require_once __DIR__ . '/../src/User.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$config = require __DIR__ . '/../config/config.php';
$pdo = new PDO(
    "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8mb4",
    $config->DB_USER,
    $config->DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interests = [
        'frontend' => intval($_POST['frontend'] ?? 0),
        'backend' => intval($_POST['backend'] ?? 0),
        'basics' => intval($_POST['basics'] ?? 0),
        'technologies' => intval($_POST['technologies'] ?? 0)
    ];

    $stmt = $pdo->prepare("UPDATE users SET interests = ? WHERE id = ?");
    $stmt->execute([json_encode($interests), $user_id]);

    $message = "Интересите са успешно обновени!";
}

$stmt = $pdo->prepare("SELECT interests FROM users WHERE id=?");
$stmt->execute([$user_id]);
$currentInterests = json_decode($stmt->fetchColumn(), true) ?? [
    'frontend'=>0, 'backend'=>0, 'basics'=>0, 'technologies'=>0
];
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Вашите интереси</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Редакция на интереси</h1>

<?php if(!empty($message)): ?>
<p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Frontend (0-5): 
        <input type="number" name="frontend" min="0" max="5" value="<?= $currentInterests['frontend'] ?>">
    </label><br><br>

    <label>Backend (0-5): 
        <input type="number" name="backend" min="0" max="5" value="<?= $currentInterests['backend'] ?>">
    </label><br><br>

    <label>Basics (0-5): 
        <input type="number" name="basics" min="0" max="5" value="<?= $currentInterests['basics'] ?>">
    </label><br><br>

    <label>Technologies (0-5): 
        <input type="number" name="technologies" min="0" max="5" value="<?= $currentInterests['technologies'] ?>">
    </label><br><br>

    <button type="submit">Запази интересите</button>
</form>

<br>
<a href="recommendation.php">Виж препоръки</a>
</body>
</html>