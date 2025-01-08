<?php

/**
 * Проверка на положительное число
 * @param void $value
 * @return string|null
 */
function validatePositiveFloat ($value): ?string
{
    if (!is_numeric($value)) {
        return "Значение должно быть числом.";
    }

    if ($value <= 0) {
        return "Число должно быть больше нуля.";
    }

    return null;
}

/**
 * Проверка на целое положительное число
 * @param int $value
 * @return string|null
 */
function validatePositiveInt (int $value): ?string {
    if (!is_numeric($value) || $value <= 0) {
        return "Шаг ставки должен быть целым числом больше 0.";
    }

    return null;
}

/**
 * Валидация даты
 * @param string $value
 * @return string|null
 */
function validateDate (string $value): ?string
{
    if (!isDateValid($value)) {
        return "Введите дату в формате 'ГГГГ-ММ-ДД'";
    }

    $dateNow = date("Y-m-d");
    $timeDiff = strtotime($value) - strtotime($dateNow);

    if ($timeDiff < 24*60*60) {
        return "Укажите дату минимум через 24 часа";
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * isDateValid('2019-01-01'); // true
 * isDateValid('2016-02-29'); // true
 * isDateValid('2019-04-31'); // false
 * isDateValid('10.10.2010'); // false
 * isDateValid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Валидация формы
 * @param array $postData данные полученные из формы
 * @param array $rules правила валидации для полей формы
 * @param array $errorMessages сообщения об ошибке для каждого поля
 * @param mysqli $dbConnection ресурс соединения
 * @return array Массив с ошибками
 */
function validateForm(array $postData, array $rules, array $errorMessages, mysqli $dbConnection): array
{
    $required = ['lot-name', 'category', 'message', 'lot-img', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    foreach ($required as $field) {
        if (empty($postData[$field]) && $field !== 'lot-img') {
            $errors[$field] = $errorMessages[$field];
        }
    }

    foreach ($rules as $field => $rule) {
        if (!empty($postData[$field]) && $rule($postData[$field])) {
            $errors[$field] = $rule($postData[$field]);
        }
    }

    if (empty($postData['category']) || $postData['category'] === 'Выберите категорию') {
        $errors['category'] = "Выберите категорию из списка";
    } else {
        $categoryId = (int)$_POST['category'];

        $categoryExistsQuery = "SELECT id FROM categories WHERE id = ?";
        $stmt = dbGetPrepareStmt($dbConnection, $categoryExistsQuery, [$categoryId]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $errors['category'] = "Выбранная категория не существует";
        }
    }

    return $errors;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     getNounPluralForm(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}
