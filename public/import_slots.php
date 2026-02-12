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
                    $date = trim($data[0]);
                    $time = trim($data[1]);
                    
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        continue;
                    }
                    
                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
                        continue;
                    }
                    
                    if (strlen($time) === 5) {
                        $time .= ':00';
                    }
                    
                    if ($date && $time) {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO slots (date, time)
                                VALUES (?, ?)
                            ");
                            
                            if ($stmt->execute([$date, $time])) {
                                $imported++;
                            }
                        } catch (PDOException $e) { }
                    }
                }
            }
            
            fclose($handle);
            $msg = "Импортирани са $imported слота успешно!";
        } else {
            $error = "Грешка при четене на файла.";
        }
    } else {
        $error = "Грешка при качване на файла.";
    }
}

if (isset($_POST['delete_all_slots'])) {
    try {
        $pdo->query("DELETE FROM slots");
        $msg = "Всички слотове са изтрити успешно!";
    } catch (PDOException $e) {
        $error = "Грешка при изтриване: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Импорт на слотове</title>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>📥 Импорт на времеви слотове от CSV файл</h1>

    <?php if ($msg): ?>
        <p style="color:green"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Инструкции</h2>
        <p>CSV файлът трябва да има следния формат:</p>
        <pre>date,time
2026-02-05,09:00:00
2026-02-05,09:06:00
2026-02-05,09:12:00
2026-02-06,09:00:00
2026-02-06,09:06:00</pre>
        
        <p><strong>Формат на датата:</strong> <code>YYYY-MM-DD</code> (напр. 2026-02-05)</p>
        <p><strong>Формат на часа:</strong> <code>HH:MM:SS</code> или <code>HH:MM</code> (напр. 09:00:00 или 09:00)</p>
        
        <p><strong>Бележки:</strong></p>
        <ul>
            <li>Първият ред (заглавие) ще бъде пропуснат</li>
            <li>Датата трябва да е във формат YYYY-MM-DD (година-месец-ден)</li>
            <li>Часът може да е HH:MM или HH:MM:SS (24-часов формат)</li>
            <li>Невалидни записи ще бъдат пропуснати</li>
        </ul>
        
        <h3 style="color:#e74c3c; margin-top:20px;">⚠️ Генериране на слотове автоматично</h3>
        <p>Можете да генерирате слотове за няколко дни наведнъж:</p>
        <ul>
            <li>Изберете начална дата, крайна дата и интервал</li>
            <li>Системата ще създаде слотове за всеки ден в интервала</li>
            <li>Пример: От 09:00 до 12:00 на всеки 6 минути</li>
        </ul>
    </div>

    <div class="card">
        <h2>Качи CSV файл</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Импортирай слотове</button>
        </form>
    </div>

    <div class="card" style="background:#fff3cd; border-left: 4px solid #ffc107;">
        <h2 style="color:#856404;">🗑️ Управление на слотове</h2>
        <p style="color:#856404;">
            <strong>Внимание!</strong> Изтриването на всички слотове ще премахне и всички резервации.
            Това действие не може да бъде отменено.
        </p>
        <form method="post" onsubmit="return confirm('Сигурни ли сте, че искате да изтриете ВСИЧКИ слотове? Това ще изтрие и всички резервации!');">
            <button type="submit" name="delete_all_slots" style="background:#e74c3c;">Изтрий всички слотове</button>
        </form>
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