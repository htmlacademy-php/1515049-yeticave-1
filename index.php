<?php

require_once 'helpers.php';
require_once 'functions.php';

$isAuth = rand(0, 1);
$userName = 'Наталья';

$con = mysqli_connect("localhost", "root", "", "yeticave");

mysqli_set_charset($con, "utf8");

$sql_lots = "SELECT l.id, l.title AS title, l.start_price AS price, l.image_url AS image_url, l.ended_at AS finish_date, c.name AS category,
       COALESCE(MAX(r.amount), l.start_price) AS current_price
FROM lots l
       JOIN categories c ON c.id = l.category_id
       LEFT JOIN rates r ON r.lot_id = l.id
WHERE l.ended_at > NOW()
GROUP BY l.id, l.title, l.start_price, l.image_url, c.name
ORDER BY l.created_at DESC;";

$result_lots = mysqli_query($con, $sql_lots);

if (!$result_lots) {
    $error = mysqli_error($con);
    print("SQL Error: $error");
}

$lots = mysqli_fetch_all($result_lots, MYSQLI_ASSOC);

$sql_categories = "SELECT * FROM categories;";
$result_categories = mysqli_query($con, $sql_categories);

if (!$result_categories) {
    $error = mysqli_error($con);
    print("SQL Error: $error");
}

$categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);

$pageContent = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Yeti Cave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
