<?php

require_once 'init.php';

$config = require 'config.php';

$dbConnection = dbConnect($config);
$lots = getLotsFromDb($dbConnection);
$categories = getCategoriesFromDb($dbConnection);
mysqli_close($dbConnection);

$isAuth = rand(0, 1);
$userName = 'Наталья';

if (empty($lots)) {
    print("На данный момент нет доступных лотов.");
}

$pageContent = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Yeti Cave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
