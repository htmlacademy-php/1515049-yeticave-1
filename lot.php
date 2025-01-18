<?php

require_once 'init.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $dbConnection */

$categories = getCategories($dbConnection);

$lotId = getLotIdFromQueryParams($dbConnection);
$lot = getLotById($dbConnection, $lotId);

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'lot' => $lot
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
