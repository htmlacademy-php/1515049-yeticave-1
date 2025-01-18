<?php

require_once 'init.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $dbConnection */


$lots = getLots($dbConnection);
$categories = getCategories($dbConnection);



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
