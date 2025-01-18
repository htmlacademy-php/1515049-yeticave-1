<?php

require_once 'init.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $dbConnection */

$categories = getCategories($dbConnection);

$requestResult = processRequest($_POST, $_FILES, $dbConnection, $categories);


$layoutContent = includeTemplate('layout.php', [
    'content' => $requestResult['content'],
    'title' => "Добавление лота",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
