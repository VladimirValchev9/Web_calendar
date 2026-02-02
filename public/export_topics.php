<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

$config = require __DIR__ . '/../config/config.php';
$pdo = new PDO(
    "mysql:host={$config->DB_HOST};dbname={$config->DB_NAME};charset=utf8mb4",
    $config->DB_USER,
    $config->DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (isset($_GET['export'])) {
    $filter = $_GET['filter'] ?? 'all';
    
    if ($filter === 'approved') {
        $stmt = $pdo->query("SELECT title, category FROM topics WHERE approved = 1 ORDER BY category, title");
    } elseif ($filter === 'pending') {
        $stmt = $pdo->query("SELECT title, category FROM topics WHERE approved = 0 ORDER BY category, title");
    } else {
        $stmt = $pdo->query("SELECT title, category FROM topics ORDER BY category, title");
    }
    
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="topics_export_' . $filter . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['title', 'category']);
    
    foreach ($topics as $topic) {
        fputcsv($output, [$topic['title'], $topic['category']]);
    }
    
    fclose($output);
    exit;
}

$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM topics WHERE approved = 1 GROUP BY category");
$categoryStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT COUNT(*) FROM topics WHERE approved = 1");
$approvedCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM topics WHERE approved = 0");
$pendingCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM topics");
$totalTopics = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Експорт на теми</title>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>📤 Експорт на теми</h1>

    <div class="card">
        <h2>Статистика</h2>
        <p>Общо теми: <strong><?= $totalTopics ?></strong></p>
        <ul>
            <li>Одобрени: <strong><?= $approvedCount ?></strong></li>
            <li>Чакащи одобрение: <strong><?= $pendingCount ?></strong></li>
        </ul>
        
        <h3>По категории (одобрени):</h3>
        <ul>
            <li>Frontend: <strong><?= $categoryStats['frontend'] ?? 0 ?></strong></li>
            <li>Backend: <strong><?= $categoryStats['backend'] ?? 0 ?></strong></li>
            <li>Basics: <strong><?= $categoryStats['basics'] ?? 0 ?></strong></li>
            <li>Technologies: <strong><?= $categoryStats['technologies'] ?? 0 ?></strong></li>
        </ul>
    </div>

    <div class="card">
        <h2>Експорт в CSV</h2>
        <p>Експортирайте теми в CSV файл с формат: <code>title,category</code></p>
        
        <div style="display:flex; gap:10px; margin-top:20px; flex-wrap:wrap;">
            <form method="get" style="flex:1; min-width:200px;">
                <input type="hidden" name="filter" value="all">
                <button type="submit" name="export" value="1" style="width:100%; background:#3498db;">
                    ⬇️ Всички теми
                </button>
            </form>
            
            <form method="get" style="flex:1; min-width:200px;">
                <input type="hidden" name="filter" value="approved">
                <button type="submit" name="export" value="1" style="width:100%; background:#27ae60;">
                    ⬇️ Само одобрени
                </button>
            </form>
            
            <form method="get" style="flex:1; min-width:200px;">
                <input type="hidden" name="filter" value="pending">
                <button type="submit" name="export" value="1" style="width:100%; background:#f39c12;">
                    ⬇️ Само чакащи
                </button>
            </form>
        </div>
    </div>

    <div class="card" style="background:#e8f5e9; border-left: 4px solid #4caf50;">
        <h2 style="color:#2e7d32;">💡 Съвет</h2>
        <p>След експортиране можете да:</p>
        <ul>
            <li>Редактирате темите в Excel/LibreOffice Calc</li>
            <li>Добавите нови теми масово</li>
            <li>Архивирате списъка с теми за семестъра</li>
            <li>Споделите темите с други преподаватели</li>
            <li>Импортирате в нова инсталация на системата</li>
        </ul>
    </div>

    <br>
    <button onclick="window.location.href='calendar.php'">← Назад към календара</button>
</div>

<style>
.container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

ul {
    margin-left: 20px;
}
</style>

</body>
</html>