<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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
    includeTemplate('templates/email.php');
    return ob_get_clean();
}
