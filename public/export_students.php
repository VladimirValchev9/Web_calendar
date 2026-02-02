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
    $stmt = $pdo->query("SELECT email, role FROM users ORDER BY role, email");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['email', 'role']);
    
    foreach ($users as $user) {
        fputcsv($output, [$user['email'], $user['role']]);
    }
    
    fclose($output);
    exit;
}

$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Експорт на студенти</title>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>📤 Експорт на потребители</h1>

    <div class="card">
        <h2>Статистика</h2>
        <p>Общо потребители: <strong><?= $totalUsers ?></strong></p>
        <ul>
            <li>Студенти: <strong><?= $stats['student'] ?? 0 ?></strong></li>
            <li>Преподаватели: <strong><?= $stats['teacher'] ?? 0 ?></strong></li>
        </ul>
    </div>

    <div class="card">
        <h2>Експорт в CSV</h2>
        <p>Експортирайте всички потребители в CSV файл с формат: <code>email,role</code></p>
        
        <p><strong>Забележка:</strong> Паролите не се експортират по соображения за сигурност. При импорт ще трябва да зададете нови пароли.</p>
        
        <form method="get">
            <button type="submit" name="export" value="1" style="background:#27ae60;">
                ⬇️ Изтегли CSV файл
            </button>
        </form>
    </div>

    <div class="card" style="background:#e8f5e9; border-left: 4px solid #4caf50;">
        <h2 style="color:#2e7d32;">💡 Съвет</h2>
        <p>След експортиране можете да:</p>
        <ul>
            <li>Редактирате данните в Excel/LibreOffice Calc</li>
            <li>Архивирате списъка с потребители</li>
            <li>Използвате го за backup преди промени</li>
            <li>Импортирате в друга система</li>
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