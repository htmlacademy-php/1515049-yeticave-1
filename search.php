<?php

require_once("init.php");

/** @var mysqli $dbConnection */
/** @var int $isAuth */
/** @var string $userName */

$categories = getCategories($dbConnection);

$searchQuery = trim($_GET['search'] ?? '');
$lots = $searchQuery ? searchLots($dbConnection, $searchQuery) : getLots($dbConnection);


$content = includeTemplate("main.php", [
    'categories' => $categories,
    'lots' => $lots,
    'searchQuery' => $searchQuery,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Поиск",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
    'searchQuery' => $searchQuery,
]);

print($layoutContent);
