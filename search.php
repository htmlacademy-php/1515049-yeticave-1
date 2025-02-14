<?php

require_once("init.php");

/** @var mysqli $dbConnection */
/** @var string $userName */
/** @var array $categories */

$searchQuery = trim($_GET['search'] ?? '');

if(empty($searchQuery)){
    header("Location: /");
    exit();
}

$lots = searchLots($dbConnection, $searchQuery);


$content = includeTemplate("main.php", [
    'categories' => $categories,
    'lots' => $lots,
    'searchQuery' => $searchQuery,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Поиск",
    'userName' => $userName,
    'categories' => $categories,
    'searchQuery' => $searchQuery,
]);

print($layoutContent);
