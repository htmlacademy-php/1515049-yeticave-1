<?php

require_once 'init.php';
require_once 'vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/** @var mysqli $dbConnection Ресурс подключения */


/**
 * Отправляет email победителю
 */
function sendWinnerEmail(string $email, string $name, string $lotTitle, int $lotId, int $userId): void
{
    // Загружаем конфигурацию
    $config = include('config.php');
    $mailerConfig = $config['mailer'];

    // Создаем транспорт с данными из конфигурации
    $transport = Transport::fromDsn(
        sprintf(
            'smtp://%s:%s@%s:%d',
            $mailerConfig['user'],
            $mailerConfig['password'],
            $mailerConfig['smtp_server'],
            $mailerConfig['smtp_port']
        )
    );

    $mailer = new Mailer($transport);

    $ratesLink = "https://localhost:8000/my-bets.php";

    // Генерация HTML-контента письма из шаблона
    $emailContent = getEmailTemplate($name, $lotTitle, $lotId, $ratesLink);

    $message = new Email();
    $message->from($mailerConfig['user']);
    $message->to($email);
    $message->subject('Ваша ставка победила');
    $message->html($emailContent);

    try {
        $mailer->send($message);
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        echo 'Ошибка отправки письма: ' . $e->getMessage();
    }
}

/**
 * Генерация HTML-шаблона письма
 */
function getEmailTemplate(string $winnerName, string $lotTitle, int $lotId, string $ratesLink): string
{
    ob_start();
    include 'templates/email.php';
    return ob_get_clean();
}

/**
 * Проверка, завершился ли аукцион, и если да, обновление победителя
 */
function handleEndedAuction(mysqli $dbConnection, int $lotId): void
{
    $lot = getLotById($dbConnection, $lotId);

    // Проверяем, завершен ли аукцион
    $isAuctionEnded = strtotime($lot['ended_at']) < time();
    if ($isAuctionEnded) {
        $winnerId = getWinnerIdFromRates($dbConnection, $lotId);

        if ($winnerId) {
            updateLotWinner($dbConnection, $lotId, $winnerId);
        }
    }
}

// Обрабатываем все лоты, у которых нет победителя
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
