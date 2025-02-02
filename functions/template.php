<?php

/**
 * Обрабатывает успешный вход пользователя.
 *
 * @param array $user Ассоциативный массив с данными авторизованного пользователя.
 *
 * @return void
 */
function handleSuccessfulLogin(array $user): void {
    $_SESSION['user'] = $user;
    header('Location: /');
    exit();
}

/**
 * Подсчитывает время до окончания показа лота
 * @param string $date
 * @return array
 *
 */
function calculatesRemainingTime(string $date): array
{
    $date_now = time();
    $date = strtotime($date);
    $time_diff = $date - $date_now;
    $hours = str_pad((floor($time_diff / (60 * 60))), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad((floor($time_diff / 60 - $hours * 60)), 2, '0', STR_PAD_LEFT);

    if ($date < $date_now) {
        $result[] = '00';
        $result[] = '00';
    }

    $result[] = $hours;
    $result[] = $minutes;
    return $result;
}

/**
 * Форматирует цену лота
 * @param int|float $price
 * @return string
 */
function formatPrice(int|float $price): string
{
    $price = number_format($price, 0, '.', ' ');
    return $price . ' ₽';
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function includeTemplate(string $name, array $data = []): string
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Получение ID лота из параметров запроса и валидация
 *
 * @param mysqli $dbConnection Соединение с базой данных
 * @return int $lotId Идентификатор лота
 */
function getLotIdFromQueryParams(mysqli $dbConnection): int
{
    $lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($lotId === null || $lotId === false || $lotId === '') {
        header("Location: /404.php");
        exit();
    }
    $lot = getLotById($dbConnection, $lotId);

    if (!$lot) {
        header("Location: /404.php");
        exit();
    }
    return $lotId;
}
