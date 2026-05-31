<?php

require 'db.php';

// Подключение к БД
$pdo = connectDB();

// Получение всех анкет вместе с языками программирования
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

// Сохранение результата запроса
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сохраненные анкеты</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>Сохраненные анкеты</h1>

    <!-- Таблица со всеми анкетами -->
    <table>

        <tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Дата рождения</th>
            <th>Пол</th>
            <th>Биография</th>
            <th>Языки</th>
        </tr>

        <!-- Вывод всех записей -->
        <?php foreach ($applications as $app): ?>

            <tr>

                <td><?= htmlspecialchars($app['id']) ?></td>

                <td><?= htmlspecialchars($app['full_name']) ?></td>

                <td><?= htmlspecialchars($app['phone']) ?></td>

                <td><?= htmlspecialchars($app['email']) ?></td>

                <td><?= htmlspecialchars($app['birth_date']) ?></td>

                <td>
                    <?= $app['gender'] === 'male'
                        ? 'Мужской'
                        : 'Женский' ?>
                </td>

                <td>
                    <?= nl2br(htmlspecialchars($app['biography'])) ?>
                </td>

                <td><?= htmlspecialchars($app['languages']) ?></td>

            </tr>

        <?php endforeach; ?>

    </table>

    <div class="links">

        <!-- Возврат на главную страницу -->
        <a href="index.php">
            Вернуться к форме
        </a>

    </div>

</div>

</body>
</html>

?>