<?php

require_once 'init.php';

$config = require 'config.php';

$dbConnection = dbConnect($config);
$lots = getLotsFromDb($dbConnection);
$categories = getCategoriesFromDb($dbConnection);
mysqli_close($dbConnection);

$isAuth = rand(0, 1);
$userName = 'Наталья';

$pageContent = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

if (empty($lots)) {
    $pageContent = "На данный момент нет доступных лотов.";
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Yeti Cave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
