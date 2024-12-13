<?php

require_once 'helpers.php';
require_once 'functions.php';
require_once 'data.php';
/** @var array $categories */
/** @var array $lots */
/** @var  $isAuth */
/** @var  $userName */

$pageContent = include_template('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

$layoutContent = include_template('layout.php', [
    'content' => $pageContent,
    'title' => "Yeti Cave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
