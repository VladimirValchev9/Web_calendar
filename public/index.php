<?php
session_start();
require_once __DIR__ . '/../src/User.php';

if (isset($_SESSION['user_id'])) {
    header('Location: calendar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    if ($user->login($_POST['email'], $_POST['password'])) {
        header('Location: calendar.php');
        exit;
    }
    $error = "Невалидни данни";
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8">
<title>Вход</title>
</head>
<body>

<div class="auth-wrapper">

    <div class="auth-form">
        <h1>Вход в системата</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red"><?= $error ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Имейл" required>
            <input type="password" name="password" placeholder="Парола" required>
            <button type="submit">Вход</button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            Нямаш акаунт?
            <a href="register.php">Регистрация</a>
        </p>
    </div>

</div>

</body>
</html>