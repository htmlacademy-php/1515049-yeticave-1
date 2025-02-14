<?php

require_once 'init.php';
require_once 'vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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
