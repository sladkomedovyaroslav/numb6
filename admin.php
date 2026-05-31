<?php

require 'db.php';

// Подключение к БД
$pdo = connectDB();

// Проверка HTTP Basic Authorization
if (!isset($_SERVER['PHP_AUTH_USER'])) {

    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');

    echo 'Требуется авторизация';

    exit();
}

// Поиск администратора по логину
$stmt = $pdo->prepare("
    SELECT * FROM admins
    WHERE login = ?
");

$stmt->execute([
    $_SERVER['PHP_AUTH_USER']
]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверка пароля администратора
if (
    !$admin ||
    !password_verify(
        $_SERVER['PHP_AUTH_PW'],
        $admin['password_hash']
    )
) {

    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');

    echo 'Неверный логин или пароль';

    exit();
}

// Удаление анкеты
if (!empty($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    // Сначала удаляем связанные языки
    $stmt = $pdo->prepare("
        DELETE FROM application_languages
        WHERE application_id = ?
    ");

    $stmt->execute([$id]);

    // Затем саму анкету
    $stmt = $pdo->prepare("
        DELETE FROM applications
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: admin.php');

    exit();
}

// Сохранение изменений анкеты
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = (int) $_POST['id'];

    // Обновление данных пользователя
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

    // Удаляем старые языки
    $stmt = $pdo->prepare("
        DELETE FROM application_languages
        WHERE application_id = ?
    ");

    $stmt->execute([$id]);

    // Добавляем выбранные языки заново
    if (!empty($_POST['languages'])) {

        $stmt = $pdo->prepare("
            INSERT INTO application_languages
            (application_id, language_id)
            VALUES (?, ?)
        ");

        foreach ($_POST['languages'] as $language_id) {

            $stmt->execute([
                $id,
                $language_id
            ]);
        }
    }

    header('Location: admin.php');

    exit();
}

// Получение всех анкет
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

// Получение статистики по языкам
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

// Получение списка всех языков
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

        <!-- Вывод статистики -->
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

        <?php

        // Получаем языки конкретного пользователя
        $stmt = $pdo->prepare("
            SELECT language_id
            FROM application_languages
            WHERE application_id = ?
        ");

        $stmt->execute([$app['id']]);

        $selected = $stmt->fetchAll(PDO::FETCH_COLUMN);

        ?>

        <!-- Форма редактирования анкеты -->
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

            <label>Телефон</label>

            <input
                type="text"
                name="phone"
                value="<?= htmlspecialchars($app['phone']) ?>"
            >

            <label>Email</label>

            <input
                type="text"
                name="email"
                value="<?= htmlspecialchars($app['email']) ?>"
            >

            <label>Дата рождения</label>

            <input
                type="date"
                name="birth_date"
                value="<?= htmlspecialchars($app['birth_date']) ?>"
            >

            <label>Пол</label>

            <select name="gender">

                <option
                    value="male"
                    <?= $app['gender'] == 'male'
                        ? 'selected'
                        : '' ?>
                >
                    Мужской
                </option>

                <option
                    value="female"
                    <?= $app['gender'] == 'female'
                        ? 'selected'
                        : '' ?>
                >
                    Женский
                </option>

            </select>

            <label>Биография</label>

            <textarea name="biography" rows="5"><?= htmlspecialchars($app['biography']) ?></textarea>

            <label>Языки программирования</label>

            <select name="languages[]" multiple>

                <?php foreach ($languages as $language): ?>

                    <option
                        value="<?= $language['id'] ?>"
                        <?= in_array($language['id'], $selected)
                            ? 'selected'
                            : '' ?>
                    >

                        <?= htmlspecialchars($language['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit">
                Сохранить изменения
            </button>

            <!-- Удаление анкеты -->
            <a
                href="admin.php?delete=<?= $app['id'] ?>"
                onclick="return confirm('Удалить запись?')"
            >
                Удалить
            </a>

        </form>

        <hr>

    <?php endforeach; ?>

</div>

</body>
</html>