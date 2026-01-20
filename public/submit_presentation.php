<?php
session_start();
require_once __DIR__ . '/../src/Presentation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$presentation = new Presentation();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $presentation->submit(
        $_POST['title'],
        $_POST['category'],
        $_SESSION['user_id']
    );
    $msg = "Темата е изпратена за одобрение.";
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<title>Нова тема</title>
</head>
<body>

<h1>Заявяване на тема за презентация</h1>

<?php if (!empty($msg)) echo "<p>$msg</p>"; ?>

<form method="post">
    <input type="text" name="title" placeholder="Име на темата" required>

    <select name="category" required>
        <option value="">-- Категория --</option>
        <option value="frontend">Front-end</option>
        <option value="backend">Back-end</option>
        <option value="basics">Базови концепции</option>
        <option value="technologies">Свързани технологии</option>
    </select>

    <button type="submit">Изпрати</button>
</form>

</body>
</html>