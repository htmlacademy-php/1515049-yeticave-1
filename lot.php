<?php
require_once 'init.php';

$isAuth = rand(0, 1);
$userName = 'Наталья';

$config = require 'config.php';

$dbConnection = dbConnect($config);
$categories = getCategoriesFromDb($dbConnection);

$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($lotId === null || $lotId === false || $lotId === '') {
    http_response_code(404);
    $content = includeTemplate('404.php', [
        'categories' => $categories
    ]);
    $lotTitle = '404 - Страница не найдена';
} else {
    $lot = getLotById($dbConnection, $lotId);

    if (!$lot) {
        http_response_code(404);
        $content = includeTemplate('404.php', [
            'categories' => $categories
        ]);
        $lotTitle = '404 - Страница не найдена';
    } else {
        $content = includeTemplate('lot.php', [
            'categories' => $categories,
            'lot' => $lot
        ]);
        $lotTitle = $lot['title'];
    }
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
