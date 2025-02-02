<?php

require_once("init.php");

/** @var mysqli $dbConnection */
/** @var int $isAuth */
/** @var string $userName */

$categories = getCategories($dbConnection);
$content = includeTemplate('login.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form = $_POST;

    $required = ['email', 'password'];
    $errors = validateLoginForm($form, $dbConnection);

    if (empty($errors)) {
        $authResult = authenticateUser($form['email'], $form['password'], $dbConnection);

        if (isset($authResult['success']) && $authResult['success'] === true) {
            $_SESSION['user'] = $authResult['user'];
            header('Location: /');
            exit();
        }

        $errors = $authResult['errors'] ?? [];
    }

    if (!empty($errors)) {
        $content = includeTemplate('login.php', [
            'form' => $form,
            'errors' => $errors
        ]);
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
