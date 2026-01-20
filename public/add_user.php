<?php
session_start();
require_once __DIR__ . '/../src/User.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->createUser(
        $_POST['email'],
        $_POST['password'],
        $_POST['role']
    );
    $msg = "Потребителят е добавен успешно.";
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Добавяне на потребител</title>
</head>
<body>

<h1>Добави потребител</h1>

<?php if (!empty($msg)) echo "<p>$msg</p>"; ?>

<form method="post">
    <input type="email" name="email" required placeholder="Имейл">
    <input type="password" name="password" required placeholder="Парола">

    <select name="role">
        <option value="student">Студент</option>
        <option value="teacher">Преподавател</option>
    </select>

    <button type="submit">Добави</button>
</form>

</body>
</html>