<?php

require_once 'init.php';

/** @var mysqli $dbConnection */
/** @var string $userName */
/** @var array $categories */

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST;

    $errors = validateSignUpForm($formData);

    if (empty($errors['email']) && !isEmailUnique($formData['email'], $dbConnection)) {
        $errors['email'] = 'Пользователь с таким e-mail уже зарегистрирован';
    }

    if (empty($errors)) {
        if (addUserToDatabase($formData, $dbConnection)) {
            header('Location: login.php');
            exit;
        } else {
            $errors['database'] = 'Ошибка записи в базу данных. Попробуйте позже.';
        }
    }
}

$pageContent = includeTemplate('sign-up.php', [
    'errors' => $errors,
    'formData' => $formData,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Регистрация",
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
