<?php

require_once("init.php");

/** @var mysqli $dbConnection */
/** @var string $userName */

$categories = getCategories($dbConnection);
$searchQuery = '';
$lots = [];

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $searchQuery = trim($_GET['search'] ?? '');
    $lots = $searchQuery ? searchLots($dbConnection, $searchQuery) : getLots($dbConnection);
}

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
