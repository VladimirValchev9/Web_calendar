<?php
session_start();
require_once __DIR__ . '/../src/User.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

$msg = '';
$error = '';
$imported = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], 'r');
        
        if ($handle) {
            $user = new User();
            $header = fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $email = trim($data[0]);
                    $password = trim($data[1]);
                    $role = isset($data[2]) ? trim($data[2]) : 'student';
                    
                    if ($email && $password) {
                        try {
                            if ($user->createUser($email, $password, $role)) {
                                $imported++;
                            }
                        } catch (Exception $e) { }
                    }
                }
            }
            
            fclose($handle);
            $msg = "Импортирани са $imported потребителя успешно!";
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
<title>Импорт на студенти</title>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <h1>📥 Импорт на студенти от CSV файл</h1>

    <?php if ($msg): ?>
        <p style="color:green"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Инструкции</h2>
        <p>CSV файлът трябва да има следния формат:</p>
        <pre>email,password,role
            student1@fmi.bg,pass123,student
            student2@fmi.bg,pass456,student
            teacher@fmi.bg,admin123,teacher
        </pre>
        
        <p><strong>Бележки:</strong></p>
        <ul>
            <li>Първият ред (заглавие) ще бъде пропуснат</li>
            <li>Роля може да е: <code>student</code> или <code>teacher</code></li>
            <li>Ако роля не е посочена, по подразбиране е <code>student</code></li>
            <li>Дублиращи се имейли ще бъдат пропуснати</li>
        </ul>
    </div>

    <div class="card">
        <h2>Качи CSV файл</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Импортирай студенти</button>
        </form>
    </div>

    <br>
    <p style="text-align:center;">
        <a href="export_students.php"><strong>📤 Експорт на студенти</strong></a> |
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