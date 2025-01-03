<?php

/**
 * Установка соединения с базой данных
 * @param array $config Настройки подключения
 * @return mysqli|bool Ресурс соединения
 */
function dbConnect(array $config):mysqli|bool
{
    if (!isset($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['database'])) {
        exit("Database configuration is incomplete.");
    }

    $dbConfig = $config['db'];

    $con = mysqli_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);

    if (!$con) {
        exit("Connection error: " . mysqli_connect_error());
    }

    mysqli_set_charset($con, "utf8");

    return $con;
}

// получение лотов
/**
 * Получение массива самых новых актуальных лотов из базы данных
 * @param mysqli $con
 * @return array
 */
function getLotsFromDb(mysqli $con)
{
    $sql = "SELECT l.id, l.title, l.start_price, l.image_url, l.ended_at, c.name AS category,
       COALESCE(MAX(r.amount), l.start_price) AS current_price
FROM lots l
       JOIN categories c ON c.id = l.category_id
       LEFT JOIN rates r ON r.lot_id = l.id
WHERE l.ended_at > NOW()
GROUP BY l.id, l.title, l.start_price, l.image_url, c.name
ORDER BY l.created_at DESC;";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print("SQL Error: $error");
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


//  получение категорий
function getCategoriesFromDb(mysqli $con)
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print("SQL Error: $error");
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function dbGetPrepareStmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else {
                if (is_string($value)) {
                    $type = 's';
                } else {
                    if (is_double($value)) {
                        $type = 'd';
                    }
                }
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}
