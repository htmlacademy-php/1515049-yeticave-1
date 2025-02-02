<?php

require_once 'init.php';

/** @var mysqli $dbConnection */
/** @var int $isAuth */
/** @var string $userName */

$categories = getCategories($dbConnection);
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$lots = getLots($dbConnection, $categoryId);

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
