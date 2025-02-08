<?php

require_once 'init.php';

/** @var string $userName */
/** @var mysqli $dbConnection */
/** @var int|string $userId */
/** @var array $categories */

$lotId = getLotIdFromQueryParams($dbConnection);
$lot = getLotById($dbConnection, $lotId);

$isAuctionEnded = strtotime($lot['ended_at']) < time();
$isLotOwner = (int) $lot['author_id'] === $userId;
$isLastRateByUser = lastRateUser($dbConnection, $lotId) === $userId;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cost'])) {
    $bidValue = trim($_POST['cost']);
    $error = validateRate($bidValue, $minRate);

    if ($error) {
        $errors['cost'] = $error;
    } else {
        addRate($dbConnection, $userId, $lotId, (int) $bidValue);
        header("Location: lot.php?id=$lotId");
        exit();
    }
}

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'userName' => $userName,
    'lot' => $lot,
    'isAuctionEnded' => $isAuctionEnded,
    'isLotOwner' => $isLotOwner,
    'isLastRateByUser' => $isLastRateByUser,
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
