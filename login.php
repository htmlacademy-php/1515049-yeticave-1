<?php

require_once("init.php");

/** @var mysqli $dbConnection */
/** @var int $isAuth */
/** @var string $userName */

$categories = getCategories($dbConnection);
$content = includeTemplate('login.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form = $_POST;

    $required = ['email', 'password'];
    $errors = [];

    foreach ($required as $field) {
        if (empty(trim($form[$field]))) {
            $errors[$field] = 'Это поле должно быть заполнено';
        }
    }

    if (!empty($errors)) {
        $content = includeTemplate('login.php', ['form' => $form, 'errors' => $errors]);
    } else {
        $email = mysqli_real_escape_string($dbConnection, $form['email']);
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($dbConnection, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($form['password'], $user['password'])) {
                $_SESSION['user'] = $user;
                header('Location: /');
                exit();
            } else {
                $errors['password'] = 'Неверный пароль';
            }
        } else {
            $errors['email'] = 'Пользователь с этим email не найден';
        }

        if (!empty($errors)) {
            $content = includeTemplate('login.php', ['form' => $form, 'errors' => $errors]);
        }
    }
} else {
    if (isset($_SESSION['user'])) {
        header('Location: /');
        exit();
    }
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Вход на сайт",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
