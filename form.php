<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №5</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>Анкета пользователя</h1>

    <p class="author">
        Выполнил: Сладкомедов Ярослав, ПМИ 23
    </p>

    <!-- Сообщение об успешном сохранении -->
    <?php if (!empty($messages)): ?>

        <div class="success">

            <?php foreach ($messages as $message): ?>

                <?= htmlspecialchars($message) ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- Вывод сгенерированных логина и пароля -->
    <?php if (!empty($generatedCredentials)): ?>

        <div class="success">

            <?= htmlspecialchars($generatedCredentials) ?>

        </div>

    <?php endif; ?>

    <!-- Основная форма -->
    <form action="" method="POST">

        <label>ФИО</label>

        <input
            type="text"
            name="full_name"
            class="<?= $errors['full_name'] ? 'error-field' : '' ?>"
            value="<?= htmlspecialchars($_COOKIE['full_name_value'] ?? '') ?>"
        >

        <!-- Ошибка для ФИО -->
        <?php if (!empty($error_messages['full_name'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['full_name']) ?>
            </div>

        <?php endif; ?>

        <label>Телефон</label>

        <input
            type="tel"
            name="phone"
            class="<?= $errors['phone'] ? 'error-field' : '' ?>"
            value="<?= htmlspecialchars($_COOKIE['phone_value'] ?? '') ?>"
        >

        <!-- Ошибка для телефона -->
        <?php if (!empty($error_messages['phone'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['phone']) ?>
            </div>

        <?php endif; ?>

        <label>Email</label>

        <input
            type="email"
            name="email"
            class="<?= $errors['email'] ? 'error-field' : '' ?>"
            value="<?= htmlspecialchars($_COOKIE['email_value'] ?? '') ?>"
        >

        <!-- Ошибка для email -->
        <?php if (!empty($error_messages['email'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['email']) ?>
            </div>

        <?php endif; ?>

        <label>Дата рождения</label>

        <input
            type="date"
            name="birth_date"
            class="<?= $errors['birth_date'] ? 'error-field' : '' ?>"
            value="<?= htmlspecialchars($_COOKIE['birth_date_value'] ?? '') ?>"
        >

        <!-- Ошибка для даты рождения -->
        <?php if (!empty($error_messages['birth_date'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['birth_date']) ?>
            </div>

        <?php endif; ?>

        <label>Пол</label>

        <div class="radio-group">

            <label>
                <input
                    type="radio"
                    name="gender"
                    value="male"
                    <?= (!empty($_COOKIE['gender_value']) && $_COOKIE['gender_value'] == 'male') ? 'checked' : '' ?>
                >
                Мужской
            </label>

            <label>
                <input
                    type="radio"
                    name="gender"
                    value="female"
                    <?= (!empty($_COOKIE['gender_value']) && $_COOKIE['gender_value'] == 'female') ? 'checked' : '' ?>
                >
                Женский
            </label>

        </div>

        <!-- Ошибка для пола -->
        <?php if (!empty($error_messages['gender'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['gender']) ?>
            </div>

        <?php endif; ?>

        <label>Любимые языки программирования</label>

        <select
            name="languages[]"
            multiple
            class="<?= $errors['languages'] ? 'error-field' : '' ?>"
        >

            <!-- Список языков из БД -->
            <?php foreach ($languages as $language): ?>

                <option value="<?= $language['id'] ?>">

                    <?= htmlspecialchars($language['name']) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <!-- Ошибка выбора языков -->
        <?php if (!empty($error_messages['languages'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['languages']) ?>
            </div>

        <?php endif; ?>

        <label>Биография</label>

        <textarea
            name="biography"
            rows="6"
        ><?= htmlspecialchars($_COOKIE['biography_value'] ?? '') ?></textarea>

        <!-- Согласие пользователя -->
        <div class="checkbox">

            <label>

                <input
                    type="checkbox"
                    name="agreement"
                >

                С контрактом ознакомлен(а)

            </label>

        </div>

        <!-- Ошибка согласия -->
        <?php if (!empty($error_messages['agreement'])): ?>

            <div class="error-message">
                <?= htmlspecialchars($error_messages['agreement']) ?>
            </div>

        <?php endif; ?>

        <!-- Кнопка сохранения или обновления -->
        <button type="submit">
            <?= !empty($_SESSION['user_id']) ? 'Обновить данные' : 'Сохранить' ?>
        </button>

    </form>

    <div class="links">

        <!-- Просмотр анкет -->
        <a href="view.php">
            Просмотреть анкеты
        </a>

        <br><br>

        <!-- Вход или выход пользователя -->
        <?php if (empty($_SESSION['user_id'])): ?>

            <a href="login.php">
                Войти
            </a>

        <?php else: ?>

            <a href="logout.php">
                Выйти
            </a>

        <?php endif; ?>

    </div>

</div>

</body>
</html>