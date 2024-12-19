<?php


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

