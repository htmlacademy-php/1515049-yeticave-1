<?php

/**
 * Получает ID победителя для лота, основываясь на последней ставке.
 *
 * @param mysqli $dbConnection Ресурс соединения с БД.
 * @param int $lotId ID лота.
 *
 * @return int|null ID пользователя, если ставка выиграла, иначе null.
 */
function getWinnerIdFromRates(mysqli $dbConnection, int $lotId): ?int
{
    $rates = getLotRates($dbConnection, $lotId);

    if ($rates) {

        return $rates[0]['user_id'];
    }

    return null;
}

/**
 * Обновляет winner_id в таблице лотов для лота, чья ставка победила
 *
 * @param mysqli $link Ресурс соединения с базой данных
 * @param int $lotId ID лота, для которого нужно обновить winner_id
 * @param int $winnerId ID пользователя, чья ставка выиграла
 *
 * @return bool Возвращает true, если обновление прошло успешно, иначе false
 */
function updateLotWinner(mysqli $link, int $lotId, int $winnerId): bool
{
    $sql = "UPDATE lots SET winner_id = ? WHERE id = ?";
    $data = [$winnerId, $lotId];
    $stmt = dbGetPrepareStmt($link, $sql, $data);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * Получает максимальную ставку для указанного лота.
 *
 * @param mysqli $dbConnection Подключение к базе данных.
 * @param int $lotId ID лота.
 * @return float Максимальная ставка.
 */
function getMaxBetForLot(mysqli $dbConnection, int $lotId): float
{
    $sql = "SELECT MAX(amount) AS max_amount FROM rates WHERE lot_id = ?";
    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return (float)($row['max_amount'] ?? 0);
}

/**
 * Получает все ставки пользователя из базы данных.
 *
 * @param mysqli $dbConnection Подключение к базе данных.
 * @param int $userId ID пользователя.
 * @return array Массив ставок пользователя.
 */
function getUserRates(mysqli $dbConnection, int $userId): array
{
    $sql = "
        SELECT
            r.id AS rate_id,
            r.amount AS rate_amount,
            r.created_at AS rate_created_at,
            l.id AS lot_id,
            l.title AS lot_title,
            l.image_url AS lot_image,
            l.ended_at AS lot_end_date,
            c.name AS category_name,
            u.contacts AS winner_contacts
        FROM
            rates r
        JOIN
            lots l ON r.lot_id = l.id
        JOIN
            categories c ON l.category_id = c.id
        JOIN
            users u ON l.author_id = u.id
        WHERE
            r.user_id = ?
        ORDER BY
            r.created_at DESC;
    ";

    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$userId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Получает список ставок для указанного лота
 *
 * @param mysqli $dbConnection Подключение к базе данных
 * @param int $lotId ID лота
 * @return array Массив ставок
 */
function getLotRates(mysqli $dbConnection, int $lotId): array {
    $sql = "SELECT r.amount, r.user_id, u.name, r.created_at
            FROM rates r
            JOIN users u ON r.user_id = u.id
            WHERE r.lot_id = ?
            ORDER BY r.created_at DESC";

    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Добавляет ставку в базу данных
 *
 * @param mysqli $dbConnection Соединение с базой данных
 * @param int $userId ID пользователя
 * @param int $lotId ID лота
 * @param int $rateValue Сумма ставки
 * @return bool Успешность добавления
 */
function addRate(mysqli $dbConnection, int $userId, int $lotId, int $rateValue): bool {
    $sql = "INSERT INTO rates (user_id, lot_id, amount, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$userId, $lotId, $rateValue]);

    return mysqli_stmt_execute($stmt);
}

/**
 * Получает ID пользователя, который сделал последнюю ставку по лоту.
 *
 * @param mysqli $dbConnection Соединение с базой данных.
 * @param int $lotId ID лота.
 * @return int|null ID пользователя или null, если ставок нет.
 */
function lastRateUser(mysqli $dbConnection, int $lotId): ?int
{
    $sql = "SELECT user_id FROM rates WHERE lot_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$lotId]);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['user_id'] ?? null;
    }

    return null;
}

/**
 * Поиск лотов по запросу
 *
 * @param mysqli $dbConnection Соединение с БД
 * @param string $searchQuery Поисковый запрос
 * @return array Найденные лоты
 */
function searchLots(mysqli $dbConnection, string $searchQuery): array
{
    $sql = "SELECT
                l.*,
                c.name AS category
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE MATCH(l.title, l.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                  AND l.ended_at > NOW()";

    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$searchQuery]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

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
 * то выполняется выборка только лотов из указанной категории.
 *
 * @param mysqli $con Подключение к базе данных.
 * @param int|null $categoryId Категории для фильтрации (по умолчанию null, если нужен полный список).
 * @return array Массив с лотами
 */

function getLots(mysqli $con, ?int $categoryId = null): array
{
    $sql = "SELECT l.id, l.title, l.start_price, l.image_url, l.created_at, l.ended_at, l.category_id,
                   c.id as category_id, c.name AS category,
                   COALESCE(MAX(r.amount), l.start_price) AS current_price
            FROM lots l
            JOIN categories c ON c.id = l.category_id
            LEFT JOIN rates r ON r.lot_id = l.id
            WHERE l.ended_at > NOW()";

    if ($categoryId !== null) {
        $sql .= " AND l.category_id = ?";
    }

    $sql .= " GROUP BY l.id, l.title, l.start_price, l.image_url, c.name, l.ended_at, l.created_at, l.category_id
              ORDER BY l.ended_at, l.created_at DESC";

    if ($categoryId === null) {
        $result = mysqli_query($con, $sql);
    } else {
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    if (!$result) {
        error_log("SQL Error: " . mysqli_error($con));
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
