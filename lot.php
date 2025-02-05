<?php

require_once 'init.php';

/** @var string $userName */
/** @var mysqli $dbConnection */

$categories = getCategories($dbConnection);

$lotId = getLotIdFromQueryParams($dbConnection);
$lot = getLotById($dbConnection, $lotId);

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'userName' => $userName,
    'lot' => $lot
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
