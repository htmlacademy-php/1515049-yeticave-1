<?php

/**
 * Ищет пользователя в базе данных по email.
 *
 * @param string $email Email пользователя для поиска.
 * @param mysqli $db Объект подключения к базе данных.
 *
 * @return array|null Ассоциативный массив с данными пользователя, если он найден, иначе null.
 */
function findUserByEmail(string $email, mysqli $db): ?array
{
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($db, $sql, [$email]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}

/**
 * Установка соединения с базой данных
 * @param array $config Настройки подключения
 * @return mysqli|bool Ресурс соединения
 */
function dbConnect(array $config): mysqli|bool
{
    if (!isset($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['database'])) {
        exit("Database configuration is incomplete.");
    }

    $dbConfig = $config['db'];

    $con = mysqli_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database'], $dbConfig['port']);

    if (!$con) {
        exit("Connection error: " . mysqli_connect_error());
    }

    mysqli_set_charset($con, "utf8");

    return $con;
}

/**
 * Функция выполняет SQL-запрос для выборки активных лотов. Если передан параметр $categoryId,
 * то выполняется выборка только лотов из указанной категории. Для главной страницы
 * (без фильтрации по категории) используется обычный `mysqli_query()`, а при фильтрации
 * по категории — подготовленный запрос (`prepared statement`).
 *
 * @param mysqli $con Подключение к базе данных.
 * @param int|null $categoryId Категории для фильтрации (по умолчанию null, если нужен полный список).
 * @return array Массив с лотами
 */

function getLots(mysqli $con, ?int $categoryId = null): array
{
    if ($categoryId === null) {
        $sql = "SELECT l.id, l.title, l.start_price, l.image_url, l.created_at, l.ended_at, c.id as category_id, c.name AS category,
                    COALESCE(MAX(r.amount), l.start_price) AS current_price
                FROM lots l
                    JOIN categories c ON c.id = l.category_id
                    LEFT JOIN rates r ON r.lot_id = l.id
                WHERE l.ended_at > NOW()
                GROUP BY l.id, l.title, l.start_price, l.image_url, c.name, l.ended_at, l.created_at, l.category_id
                ORDER BY l.ended_at, l.created_at DESC;";

        $result = mysqli_query($con, $sql);
    } else {
        $sql = "SELECT l.id, l.title, l.start_price, l.image_url, l.created_at, l.ended_at, c.id as category_id, c.name AS category,
                    COALESCE(MAX(r.amount), l.start_price) AS current_price
                FROM lots l
                    JOIN categories c ON c.id = l.category_id
                    LEFT JOIN rates r ON r.lot_id = l.id
                WHERE l.ended_at > NOW() AND l.category_id = ?
                GROUP BY l.id, l.title, l.start_price, l.image_url, c.name, l.ended_at, l.created_at, l.category_id
                ORDER BY l.ended_at, l.created_at DESC;";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


/**
 * Получение списка категорий
 * @param mysqli $con
 * @return array
 */
function getCategories(mysqli $con): array
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Добавление нового лота в базу данных
 *
 * @param array $lotData Отвалидированные данные из формы
 * @param mysqli $con ресурс соединения
 * @return array массив успех|id нового лота|ошибка
 */
function addLotToDb(array $lotData, mysqli $con): array
{
    $response = [
        'success' => false,
        'lotId' => null,
        'error' => null
    ];

    $sql = 'INSERT INTO lots (created_at, title, category_id, description, image_url, start_price, rate_step, author_id, ended_at)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = dbGetPrepareStmt($con, $sql, $lotData);

    if (!mysqli_stmt_execute($stmt)) {
        $response['error'] = "Ошибка выполнения запроса: " . mysqli_error($con);
        return $response;
    }

    $response['success'] = true;
    $response['lotId'] = mysqli_insert_id($con);

    return $response;
}

/**
 * Получение лота по id
 *
 * @param mysqli $con
 * @param int $id
 *
 * @return array|false|null
 */
function getLotById(mysqli $con, int $id): array|false|null
{
    if ($id < 0 || $id == null || $id == '') {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    $sql = "SELECT  l.*,
                    c.name AS category,
                    r.amount AS last_rate,
                    l.rate_step
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN rates r ON l.id = r.lot_id
            WHERE l.id = $id
            ORDER BY r.created_at DESC
            LIMIT 1;";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    return mysqli_fetch_assoc($result);
}

/**
 * Добавление нового пользователя в базу данных
 *
 * @param array $formData Данные формы
 * @param mysqli $dbConnection Объект подключения к базе данных
 * @return bool true, если пользователь успешно добавлен, иначе false
 */
function addUserToDatabase(array $formData, mysqli $dbConnection): bool
{
    $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, name, password, contacts) VALUES (?, ?, ?, ?)";
    $stmt = dbGetPrepareStmt($dbConnection, $sql, [
        $formData['email'],
        $formData['name'],
        $passwordHash,
        $formData['contacts']
    ]);

    return mysqli_stmt_execute($stmt);
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
