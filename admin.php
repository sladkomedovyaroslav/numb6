<?php

require 'db.php';

$pdo = connectDB();

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW'])) {

    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');

    echo 'Требуется авторизация';
    exit();
}

$stmt = $pdo->prepare("
    SELECT * FROM admins
    WHERE login = ?
");

$stmt->execute([$_SERVER['PHP_AUTH_USER']]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

echo '<pre>';

print_r($admin);

echo '</pre>';

echo password_verify(
    $_SERVER['PHP_AUTH_PW'],
    $admin['password_hash']
)
? 'PASSWORD OK'
: 'PASSWORD BAD';

exit();

if (!empty($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("
        DELETE FROM application_languages
        WHERE application_id = ?
    ");

    $stmt->execute([$id]);

    $stmt = $pdo->prepare("
        DELETE FROM applications
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = (int) $_POST['id'];

    $stmt = $pdo->prepare("
        UPDATE applications
        SET
            full_name = ?,
            phone = ?,
            email = ?,
            birth_date = ?,
            gender = ?,
            biography = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'],
        $id
    ]);

    $stmt = $pdo->prepare("
        DELETE FROM application_languages
        WHERE application_id = ?
    ");

    $stmt->execute([$id]);

    if (!empty($_POST['languages'])) {

        $stmt = $pdo->prepare("
            INSERT INTO application_languages
            (application_id, language_id)
            VALUES (?, ?)
        ");

        foreach ($_POST['languages'] as $language_id) {

            $stmt->execute([$id, $language_id]);
        }
    }

    header('Location: admin.php');
    exit();
}

$stmt = $pdo->query("
    SELECT 
        a.*,
        GROUP_CONCAT(pl.name SEPARATOR ', ') AS languages
    FROM applications a

    LEFT JOIN application_languages al
        ON a.id = al.application_id

    LEFT JOIN programming_languages pl
        ON al.language_id = pl.id

    GROUP BY a.id

    ORDER BY a.id DESC
");

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT
        pl.name,
        COUNT(al.application_id) AS total
    FROM programming_languages pl

    LEFT JOIN application_languages al
        ON pl.id = al.language_id

    GROUP BY pl.id
");

$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$languages = $pdo->query("
    SELECT * FROM programming_languages
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>Админ-панель</h1>

    <h2>Статистика языков</h2>

    <table>

        <tr>
            <th>Язык</th>
            <th>Количество пользователей</th>
        </tr>

        <?php foreach ($stats as $stat): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($stat['name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($stat['total']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

    <h2>Все анкеты</h2>

    <?php foreach ($applications as $app): ?>

        <form method="POST" class="admin-form">

            <input
                type="hidden"
                name="id"
                value="<?= $app['id'] ?>"
            >

            <label>ФИО</label>

            <input
                type="text"
                name="full_name"
                value="<?= htmlspecialchars($app['full_name']) ?>"
            >

            <button type="submit">
                Сохранить
            </button>

            <a href="admin.php?delete=<?= $app['id'] ?>">
                Удалить
            </a>

        </form>

        <hr>

    <?php endforeach; ?>

</div>

</body>
</html>