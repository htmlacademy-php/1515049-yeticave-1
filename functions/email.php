<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * Отправляет email победителю
 */
function sendWinnerEmail(string $email, string $name, string $lotTitle, int $lotId): void
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

    $emailContent = getEmailTemplate([
        'winnerName' => $name,
        'lotTitle' => $lotTitle,
        'lotId' => $lotId,
        'ratesLink' => $config['site']['base_url'] . "/my-bets.php"
    ], $config);

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
function getEmailTemplate(array $params, array $config): string
{
    $baseUrl = $config['site']['base_url'];
    $params['lotLink'] = $baseUrl . "/lot.php?id=" . $params['lotId'];

    return includeTemplate('email.php', $params);
}
