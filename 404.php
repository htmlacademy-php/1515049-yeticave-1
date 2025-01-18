<?php

require_once 'init.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $dbConnection */

http_response_code(404);

$categories = getCategories($dbConnection);

$pageContent = includeTemplate('404.php', [
    'categories' => $categories,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "404-страница не найдена",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
