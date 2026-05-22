<?php

session_start();

require 'db.php';

$pdo = connectDB();

$stmt = $pdo->query("SELECT * FROM programming_languages ORDER BY name");
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isAuth = !empty($_SESSION['user_id']);

$currentUser = null;

if ($isAuth) {

    $stmt = $pdo->prepare("
        SELECT * FROM applications
        WHERE id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $messages = [];

    $generatedCredentials = '';

    if (!empty($_COOKIE['generated_login']) &&
        !empty($_COOKIE['generated_password'])) {

        $generatedCredentials =
            'Ваш логин: ' .
            $_COOKIE['generated_login'] .
            ' | Пароль: ' .
            $_COOKIE['generated_password'];

        setcookie('generated_login', '', time() - 3600);
        setcookie('generated_password', '', time() - 3600);
    }

    if (!empty($_COOKIE['save'])) {

        setcookie('save', '', time() - 3600);

        $messages[] = 'Данные успешно сохранены.';
    }

    if ($isAuth && $currentUser) {

        $_COOKIE['full_name_value'] = $currentUser['full_name'];
        $_COOKIE['phone_value'] = $currentUser['phone'];
        $_COOKIE['email_value'] = $currentUser['email'];
        $_COOKIE['birth_date_value'] = $currentUser['birth_date'];
        $_COOKIE['gender_value'] = $currentUser['gender'];
        $_COOKIE['biography_value'] = $currentUser['biography'];
    }

    $errors = [
        'full_name' => !empty($_COOKIE['full_name_error']),
        'phone' => !empty($_COOKIE['phone_error']),
        'email' => !empty($_COOKIE['email_error']),
        'birth_date' => !empty($_COOKIE['birth_date_error']),
        'gender' => !empty($_COOKIE['gender_error']),
        'languages' => !empty($_COOKIE['languages_error']),
        'agreement' => !empty($_COOKIE['agreement_error'])
    ];

    $error_messages = [];

    foreach ($errors as $field => $value) {

        if ($value) {

            $error_messages[$field] = $_COOKIE[$field . '_error'];

            setcookie($field . '_error', '', time() - 3600);
        }
    }

    include 'form.php';
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$gender = $_POST['gender'] ?? '';
$biography = trim($_POST['biography'] ?? '');
$agreement = isset($_POST['agreement']);
$selected_languages = $_POST['languages'] ?? [];

$hasErrors = false;

if (empty($full_name) || !preg_match('/^[а-яА-Яa-zA-Z\s\-]+$/u', $full_name)) {

    setcookie(
        'full_name_error',
        'Допустимы только буквы, пробелы и дефис.',
        0
    );

    $hasErrors = true;
}

if (empty($phone) || !preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {

    setcookie(
        'phone_error',
        'Допустимы цифры, пробелы и символы + - ( )',
        0
    );

    $hasErrors = true;
}

if (empty($email) || !preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {

    setcookie(
        'email_error',
        'Введите корректный email.',
        0
    );

    $hasErrors = true;
}

if (empty($birth_date)) {

    setcookie(
        'birth_date_error',
        'Укажите дату рождения.',
        0
    );

    $hasErrors = true;
}

if (!in_array($gender, ['male', 'female'])) {

    setcookie(
        'gender_error',
        'Выберите пол.',
        0
    );

    $hasErrors = true;
}

if (empty($selected_languages)) {

    setcookie(
        'languages_error',
        'Выберите хотя бы один язык.',
        0
    );

    $hasErrors = true;
}

if (!$agreement) {

    setcookie(
        'agreement_error',
        'Необходимо согласие.',
        0
    );

    $hasErrors = true;
}

setcookie('full_name_value', $full_name, time() + 60 * 60 * 24 * 365);
setcookie('phone_value', $phone, time() + 60 * 60 * 24 * 365);
setcookie('email_value', $email, time() + 60 * 60 * 24 * 365);
setcookie('birth_date_value', $birth_date, time() + 60 * 60 * 24 * 365);
setcookie('gender_value', $gender, time() + 60 * 60 * 24 * 365);
setcookie('biography_value', $biography, time() + 60 * 60 * 24 * 365);

if ($hasErrors) {

    header('Location: index.php');
    exit();
}

try {

    $pdo->beginTransaction();

    if ($isAuth) {

        $stmt = $pdo->prepare("
            UPDATE applications
            SET
                full_name = ?,
                phone = ?,
                email = ?,
                birth_date = ?,
                gender = ?,
                biography = ?,
                agreement = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $full_name,
            $phone,
            $email,
            $birth_date,
            $gender,
            $biography,
            $agreement ? 1 : 0,
            $_SESSION['user_id']
        ]);

        $application_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            DELETE FROM application_languages
            WHERE application_id = ?
        ");

        $stmt->execute([$application_id]);

    } else {

        $generatedLogin = 'user_' . time();

        $generatedPassword = bin2hex(random_bytes(4));

        $passwordHash = password_hash(
            $generatedPassword,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO applications
            (
                full_name,
                phone,
                email,
                birth_date,
                gender,
                biography,
                agreement,
                login,
                password_hash
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $full_name,
            $phone,
            $email,
            $birth_date,
            $gender,
            $biography,
            $agreement ? 1 : 0,
            $generatedLogin,
            $passwordHash
        ]);

        $application_id = $pdo->lastInsertId();

        setcookie(
            'generated_login',
            $generatedLogin,
            time() + 60
        );

        setcookie(
            'generated_password',
            $generatedPassword,
            time() + 60
        );
    }

    $stmt = $pdo->prepare("
        INSERT INTO application_languages
        (application_id, language_id)
        VALUES (?, ?)
    ");

    foreach ($selected_languages as $language_id) {

        $stmt->execute([
            $application_id,
            $language_id
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {

    die('Ошибка: ' . $e->getMessage());
}

setcookie('save', '1');

header('Location: index.php');
exit();
?>