<?php

require_once 'init.php';

/** @var string $userName */
/** @var mysqli $dbConnection */
/** @var int|string $userId */
/** @var array $categories */
/** @var $pagination */

$rates = getUserRates($dbConnection, $userId);

$processedRates = [];
foreach ($rates as $rate) {
    $lotEndDate = new DateTime($rate['lot_end_date']);
    $now = new DateTime();
    $isLotEnded = $now > $lotEndDate;
    $isRateWinning = $isLotEnded && ($rate['rate_amount'] == getMaxBetForLot($dbConnection, $rate['lot_id']));

    $remainingTime = $isLotEnded ? ['00', '00'] : calculatesRemainingTime($rate['lot_end_date']);

    $processedRates[] = [
        'lot_id' => $rate['lot_id'],
        'lot_title' => sanitizeInput($rate['lot_title']),
        'lot_image' => sanitizeInput($rate['lot_image']),
        'category_name' => sanitizeInput($rate['category_name']),
        'rate_amount' => $rate['rate_amount'],
        'rate_created_at' => $rate['rate_created_at'],
        'isLotEnded' => $isLotEnded,
        'isRateWinning' => $isRateWinning,
        'remaining_time' => $remainingTime,
        'formatted_price' => formatPrice($rate['rate_amount']),
        'contacts' => $rate['winner_contacts'],
        'time_ago' => timeAgo($rate['rate_created_at']),
    ];
}

$content = includeTemplate('my-bets.php', [
    'rates' => $processedRates,
    'userName' => sanitizeInput($userName),
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => 'Мои ставки',
    'userName' => sanitizeInput($userName),
    'categories' => $categories,
    'pagination' => $pagination,
]);

print($layoutContent);
