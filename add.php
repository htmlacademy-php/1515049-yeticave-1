<?php

require_once 'init.php';

$config = require 'config.php';

$dbConnection = dbConnect($config);
$categories = getCategoriesFromDb($dbConnection);
//mysqli_close($dbConnection);

$isAuth = rand(0, 1);
$userName = 'Наталья';

$pageContent = includeTemplate('add.php', [
    'categories' => $categories,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['lot-name', 'category', 'message', 'lot-img', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

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

    $lot = filter_input_array(INPUT_POST, ['lot-rate' => FILTER_DEFAULT, 'lot-step' => FILTER_DEFAULT, 'lot-date' => FILTER_DEFAULT], true);

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "Поле обязательно для заполнения";
        }
    }

    foreach ($lot as $field => $validationFunc) {
        if (isset($rules[$field])) {
            if (empty($validationFunc)) {
                $errors[$field] = "Поле обязательно для заполнения";
                continue;
            }

            $rule = $rules[$field];
            $errors[$field] = $rule($validationFunc);
        }
    }

    if (empty($_POST['category']) || $_POST['category'] === 'Выберите категорию') {
        $errors['category'] = "Выберите категорию из списка";
    } else {
        $categoryId = (int)$_POST['category'];

        $categoryExistsQuery = "SELECT id FROM categories WHERE id = ?";
        $stmt = dbGetPrepareStmt($dbConnection, $categoryExistsQuery, [$categoryId]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $errors['category'] = "Выбранная категория не существует";
        }
    }

    $errors = array_filter($errors);

    $fileName = $_FILES['lot-img']['name'];

    if (!isset($_FILES['lot-img']) || empty($_FILES['lot-img']['name'])) {
        $errors['file'] = "Загрузите изображение, это обязательно!";
    } else {
        $allowedMimeTypes = ['image/jpeg', 'image/png'];

        $fileTmpPath = $_FILES['lot-img']['tmp_name'];
        $fileMimeType = mime_content_type($fileTmpPath);

        if (in_array($fileMimeType, $allowedMimeTypes)) {
            $fileExtension = $fileMimeType === 'image/jpeg' ? '.jpg' : '.png';
            $fileName = uniqid() . $fileExtension;
            move_uploaded_file($_FILES['lot-img']['tmp_name'], 'uploads/' . $fileName);
            unset($errors['lot-img']);
        } else {
            $errors['file'] = "Изображение должно быть в формате jpg, jpeg или png";
        }
    }

    if (count($errors) > 0) {
        $pageContent = includeTemplate('add.php', ['lot' => $_POST, 'categories' => $categories, 'errors' => $errors]);
    } else {
        $newLotData = $_POST;

        $newLotData = [
            $_POST['lot-name'],         // title
            (int)$_POST['category'],   // category_id
            $_POST['message'],          // description
            'uploads/' . $fileName,     // image_url
            (float)$_POST['lot-rate'], // start_price
            (int)$_POST['lot-step'],   // rate_step
            $_POST['lot-date'],         // ended_at
        ];

        $sql = 'INSERT INTO lots (created_at, title, category_id, description, image_url, start_price, rate_step, author_id, ended_at) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 1, ?)';

        $stmt = dbGetPrepareStmt($dbConnection, $sql, $newLotData);

        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            $error = mysqli_error($dbConnection);
            die('Ошибка выполнения запроса: ' . $error);
        }


        if ($result) {
            $lotId = mysqli_insert_id($dbConnection);
            header('Location: lot.php?id=' . $lotId);
        } else {
            $pageContent = includeTemplate('add.php', ['error' => mysqli_error($dbConnection)]);
        }
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
