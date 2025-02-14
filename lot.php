<?php

require_once 'init.php';

/** @var string $userName */
/** @var mysqli $dbConnection */
/** @var int|string $userId */
/** @var array $categories */

$lotId = getLotIdFromQueryParams($dbConnection);
$lot = getLotById($dbConnection, $lotId);

$remainingTime = calculatesRemainingTime($lot["ended_at"]);
$hours = $remainingTime[0];
$minutes = $remainingTime[1];
$class = ($hours < 1) ? 'timer--finishing' : '';

$lotPrices = calculateLotPrices($lot);
$currentPrice = $lotPrices['current_price'];
$minRate = $lotPrices['min_rate'];

$isLotOwner = (int) $lot['author_id'] === $userId;
$isLastRateByUser = lastRateUser($dbConnection, $lotId) === $userId;
$errors = [];

handleEndedAuction($dbConnection, $lotId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cost'])) {

    if (!$userId) {
        http_response_code(403);
        exit('Вы должны войти, чтобы делать ставки.');
    }

    $rateValue = trim($_POST['cost']);

    $lotPrices = calculateLotPrices($lot);
    $minRate = $lotPrices['min_rate'];

    $error = validateRate($rateValue, $minRate);

    if ($error) {
        $errors['cost'] = $error;
    } else {
        addRate($dbConnection, $userId, $lotId, (int) $rateValue);
        header("Location: lot.php?id=$lotId");
        exit();
    }
}

$rates = getLotRates($dbConnection, $lotId);

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'userName' => $userName,
    'lot' => $lot,
    'hours' => $hours,
    'minutes' => $minutes,
    'class' => $class,
    'currentPrice' => $currentPrice,
    'minRate' => $minRate,
    'errors' => $errors,
    'lotId' => $lotId,
    'isAuctionEnded' => strtotime($lot['ended_at']) < time(),
    'isLotOwner' => $isLotOwner,
    'isLastRateByUser' => $isLastRateByUser,
    'rates' => $rates,
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
