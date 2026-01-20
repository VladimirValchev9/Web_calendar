<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: calendar.php');
    exit;
}

require_once __DIR__ . '/../src/User.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    try {
        $ok = $user->createUser($_POST['email'], $_POST['password'], 'student');
        if ($ok) {
            $success = "Регистрацията е успешна. Можеш да влезеш.";
        }
    } catch (PDOException $e) {
        $error = "Този имейл вече съществува.";
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>

<div class="auth-wrapper">

    <div class="auth-form">
        <h1>Регистрация</h1>

        <?php if ($success): ?>
            <p style="color:green"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Имейл" required>
            <input type="password" name="password" placeholder="Парола" required>
            <button type="submit">Регистрация</button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            Имаш акаунт? <a href="index.php">Вход</a>
        </p>
    </div>

</div>

</body>
</html>