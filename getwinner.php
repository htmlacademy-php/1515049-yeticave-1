<?php

require_once 'init.php';
require_once 'vendor/autoload.php';

/** @var mysqli $dbConnection Ресурс подключения */
/** @var array $config Данные конфигурации*/

$lots = getLotsWithoutWinners($dbConnection);

if (!empty($lots)) {
    foreach ($lots as $lot) {
        handleEndedAuction($dbConnection, $lot['id']);
        $winnerId = getWinnerIdFromRates($dbConnection, $lot['id']);
        sendWinnerEmail([
            'email' => $lot['email'],
            'name' => $lot['name'],
            'lotTitle' => $lot['title'],
            'lotId' => $lot['id'],
            'config' => $config
        ]);
    }
} else {
    return;
}
