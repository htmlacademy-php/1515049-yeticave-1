<?php

require_once 'init.php';
require_once 'vendor/autoload.php';

/** @var mysqli $dbConnection Ресурс подключения */

$lots = getLotsWithoutWinners($dbConnection);

if (!empty($lots)) {
    foreach ($lots as $lot) {
        handleEndedAuction($dbConnection, $lot['id']);
        $winnerId = getWinnerIdFromRates($dbConnection, $lot['id']);
        sendWinnerEmail($lot['email'], $lot['name'], $lot['title'], $lot['id'], $winnerId);
    }
} else {
    return;
}
