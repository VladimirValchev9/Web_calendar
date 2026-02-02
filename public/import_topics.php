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

$msg = '';
$error = '';
$imported = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], 'r');
        
        if ($handle) {
            $header = fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $title = trim($data[0]);
                    $category = trim(strtolower($data[1]));
                    
                    $validCategories = ['frontend', 'backend', 'basics', 'technologies'];
                    if (!in_array($category, $validCategories)) {
                        continue;
                    }
                    
                    if ($title && $category) {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO topics (title, category, approved)
                                VALUES (?, ?, 1)
                            ");
                            
                            if ($stmt->execute([$title, $category])) {
                                $imported++;
                            }
                        } catch (PDOException $e) { }
                    }
                }
            }
            
            fclose($handle);
            $msg = "Импортирани са $imported теми успешно!";
        } else {
            $error = "Грешка при четене на файла.";
        }
    } else {
        $error = "Грешка при качване на файла.";
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Импорт на теми</title>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>📥 Импорт на теми от CSV файл</h1>

    <?php if ($msg): ?>
        <p style="color:green"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Инструкции</h2>
        <p>CSV файлът трябва да има следния формат:</p>
        <pre>title,category
            CSS Basics,frontend
            JavaScript Modules,frontend
            REST API с PHP,backend
            Docker Fundamentals,technologies
            HTTP Protocol,basics
        </pre>
        
        <p><strong>Валидни категории:</strong></p>
        <ul>
            <li><code>frontend</code> - Front-end технологии</li>
            <li><code>backend</code> - Back-end технологии</li>
            <li><code>basics</code> - Базови концепции</li>
            <li><code>technologies</code> - Свързани технологии</li>
        </ul>
        
        <p><strong>Бележки:</strong></p>
        <ul>
            <li>Първият ред (заглавие) ще бъде пропуснат</li>
            <li>Категорията не е case-sensitive (може да е Frontend, FRONTEND, frontend)</li>
            <li>Дублиращи се заглавия ще бъдат пропуснати</li>
            <li>Всички импортирани теми са автоматично одобрени</li>
        </ul>
    </div>

    <div class="card">
        <h2>Качи CSV файл</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Импортирай теми</button>
        </form>
    </div>

    <br>
    <p style="text-align:center;">
        <a href="export_topics.php"><strong>📤 Експорт на теми</strong></a> |
        <a href="calendar.php">← Назад към календара</a>
    </p>
</div>

<style>
.container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

pre {
    background: #f4f4f4;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
}

ul {
    margin-left: 20px;
}

input[type="file"] {
    display: block;
    margin-bottom: 15px;
}
</style>

</body>
</html>