<?php

require_once 'init.php';

$config = require 'config.php';

$dbConnection = dbConnect($config);
$categories = getCategoriesFromDb($dbConnection);

$isAuth = rand(0, 1);
$userName = 'Наталья';

$pageContent = includeTemplate('add.php', [
    'categories' => $categories,
]);

$errorMessages = [
    'lot-name' => 'Введите наименование лота',
    'category' => 'Выберите категорию',
    'message' => 'Напишите описание лота',
    'lot-img' => 'Загрузите изображение',
    'lot-rate' => 'Введите начальную цену',
    'lot-step' => 'Введите шаг ставки',
    'lot-date' => 'Введите дату завершения торгов'
];

$rules = [
    'lot-rate' => function ($value) {
        return validatePositiveFloat($value);
    },
    'lot-step' => function ($value) {
        return validatePositiveInt($value);
    },
    'lot-date' => function ($value) {
        return validateDate($value);
    }
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateForm($_POST, $rules, $errorMessages, $dbConnection);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($_FILES['lot-img'], 'uploads');

        if ($fileName === null) {
            $errors['file'] = "Ошибка при загрузке изображения. Убедитесь, что файл выбран и имеет допустимый формат.";
        }
    }
    if (empty($errors)) {
        $newLotData = [
            $_POST['lot-name'],         // title
            (int)$_POST['category'],    // category_id
            $_POST['message'],          // description
            'uploads/' . $fileName,     // image_url
            (float)$_POST['lot-rate'],  // start_price
            (int)$_POST['lot-step'],    // rate_step
            $_POST['lot-date'],         // ended_at
        ];

        $result = addLotToDb($newLotData, $dbConnection);

        if ($result['success']) {
            header('Location: lot.php?id=' . $result['lotId']);
            exit;
        } else {
            $errors['database'] = $result['error'];
        }
    }

    if (count($errors) > 0) {
        $pageContent = includeTemplate('add.php', [
            'lot' => $_POST,
            'categories' => $categories,
            'errors' => $errors]);
    } else {
        $newLotData = $_POST;
    }
}


$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Добавление лота",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
