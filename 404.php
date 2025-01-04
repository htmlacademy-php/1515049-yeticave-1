<?php

$isAuth = rand(0, 1);
$userName = 'Наталья';

$pageContent = includeTemplate('404.php');

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "404-страница не найдена",
    'isAuth' => $isAuth,
    'userName' => $userName
]);

print($layoutContent);
