<?php
session_start();
require_once __DIR__ . '/../src/Recommendation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];

$config = include __DIR__ . '/../config/config.php';
$pdo = new PDO(
    "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8mb4",
    $config->DB_USER,
    $config->DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interests = [
        'frontend' => (int)($_POST['frontend'] ?? 0),
        'backend' => (int)($_POST['backend'] ?? 0),
        'basics' => (int)($_POST['basics'] ?? 0),
        'technologies' => (int)($_POST['technologies'] ?? 0),
    ];

    $stmt = $pdo->prepare("UPDATE users SET interests = ? WHERE id = ?");
    $stmt->execute([json_encode($interests), $userId]);

    $message = "Интересите са запазени успешно.";
}

$stmt = $pdo->prepare("SELECT interests FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userInterests = json_decode($user['interests'] ?? '{}', true);
$userInterests = array_merge([
    'frontend' => 0,
    'backend' => 0,
    'basics' => 0,
    'technologies' => 0
], $userInterests);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM presentations WHERE student_id = ?");
$stmt->execute([$userId]);
$hasPresentation = $stmt->fetchColumn() > 0;

$stmt = $pdo->query("
    SELECT id, title, category
    FROM topics
    WHERE approved = 1
");
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$recommended = [];
if (!$hasPresentation) {
    $rec = new Recommendation();
    $recommended = $rec->getRecommendations($userInterests, $topics, 5);
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Препоръчани теми</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<h1>Препоръчани теми</h1>

<div class="card">
    <h2>🧠 Твоите интереси</h2>

    <?php if (!empty($message)): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="interests-form">
        <label>Frontend (0–5)
            <input type="number" name="frontend" min="0" max="5" value="<?= $userInterests['frontend'] ?>">
        </label>

        <label>Backend (0–5)
            <input type="number" name="backend" min="0" max="5" value="<?= $userInterests['backend'] ?>">
        </label>

        <label>Basics (0–5)
            <input type="number" name="basics" min="0" max="5" value="<?= $userInterests['basics'] ?>">
        </label>

        <label>Technologies (0–5)
            <input type="number" name="technologies" min="0" max="5" value="<?= $userInterests['technologies'] ?>">
        </label>

        <button type="submit">Запази интересите</button>
    </form>
</div>

<?php if ($hasPresentation): ?>
    <p class="hint">
        ✅ Вече имаш избрана тема и не получаваш препоръки.
    </p>
<?php elseif (empty($recommended)): ?>
    <p class="hint">
        Няма теми, които съвпадат с интересите ти.
    </p>
<?php else: ?>
    <div class="card">
        <h2>⭐ Подходящи теми за теб</h2>

        <?php foreach ($recommended as $topic): ?>
            <div class="topic-row">
                <div class="topic-info">
                    <strong><?= htmlspecialchars($topic['title']) ?></strong>
                    <span class="badge"><?= htmlspecialchars($topic['category']) ?></span>
                </div>

                <div class="match">
                    <div class="bar">
                        <div class="fill" style="width: <?= round($topic['match_score']) ?>%"></div>
                    </div>
                    <span><?= round($topic['match_score'], 1) ?>%</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>