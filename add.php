<?php

require_once 'init.php';

/** @var string $userName */
/** @var mysqli $dbConnection */
/** @var array $categories */

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Доступ запрещён. Пожалуйста, войдите в систему.');
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handleAddLotForm($_POST, $_FILES, $dbConnection, $categories, $_SESSION['user_id']);

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }

    $content = $result['content'];
} else {
    $content = includeTemplate('add.php', [
        'categories' => $categories,
    ]);
}


$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Добавление лота",
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
