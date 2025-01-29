<?php

require_once("init.php");

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $dbConnection */
/** @var array $categories */

$content = '';

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Вход на сайт",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
