<?php

session_start();

require 'db.php';

$pdo = connectDB();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("
        SELECT * FROM applications
        WHERE login = ?
    ");

    $stmt->execute([$login]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {

        $_SESSION['user_id'] = $user['id'];

        header('Location: index.php');
        exit();

    } else {

        $error = 'Неверный логин или пароль.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>Вход</h1>

    <?php if ($error): ?>

        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Логин</label>

        <input type="text" name="login">

        <label>Пароль</label>

        <input type="password" name="password">

        <button type="submit">
            Войти
        </button>

    </form>

</div>

</body>
</html>