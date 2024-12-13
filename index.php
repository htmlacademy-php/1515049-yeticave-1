<?php

require_once 'helpers.php';
require_once 'functions.php';
require_once 'data.php';
/** @var array $categories */
/** @var array $lots */
/** @var  $isAuth */
/** @var  $userName */

$page_content = include_template('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'title' => "Yeti Cave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layout_content);
