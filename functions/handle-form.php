<?php


/**
 * Обработка формы добавления лота
 *
 * @param array $postData Данные из формы
 * @param array $fileData Данные о загруженных файлах
 * @param mysqli $dbConnection Соединение с базой данных
 * @param array $categories Список категорий (для валидации)
 * @return array Массив с результатом обработки ['success' => bool, 'content' => string, 'errors' => array]
 */
function handleAddLotForm(array $postData, array $fileData, mysqli $dbConnection, array $categories): array
{
    $errors = validateAddLotForm($postData, $dbConnection);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($fileData['lot-img'], 'uploads');

        if ($fileName === null) {
            $errors['file'] = "Ошибка при загрузке изображения. Убедитесь, что файл выбран и имеет формат jpg, jpeg или png.";
        }
    }

    if (empty($errors)) {
        $newLotData = [
            $postData['lot-name'],         // title
            (int)$postData['category'],    // category_id
            $postData['message'],          // description
            'uploads/' . $fileName,        // image_url
            (float)$postData['lot-rate'],  // start_price
            (int)$postData['lot-step'],    // rate_step
            $postData['lot-date'],         // ended_at
        ];

        $result = addLotToDb($newLotData, $dbConnection);

        if ($result['success']) {
            return [
                'success' => true,
                'redirect' => 'lot.php?id=' . $result['lotId']
            ];
        } else {
            $errors['database'] = $result['error'];
        }
    }

    return [
        'success' => false,
        'content' => includeTemplate('add.php', [
            'lotData' => $postData,
            'categories' => $categories,
            'errors' => $errors,
        ]),
        'errors' => $errors
    ];
}

/**
 * Проверка запроса на метод POST
 *
 * @param array $postData Данные из формы
 * @param array $fileData Данные файла
 * @param mysqli $dbConnection Соединение с базой данных
 * @param array $categories
 * @return array Массив с результатами:
 *               - Если запрос был POST, то возвращает массив с ключами:
 *                  - 'success' (bool): Успех операции.
 *                  - 'redirect' (string): URL, на который нужно выполнить редирект в случае успешной операции.
 *                  - 'content' (string): HTML-контент, который будет выведен на странице.
 *                - Если запрос не был POST, возвращается шаблон страницы с формой добавления лота.
 */
function processRequest(array $postData, array $fileData, mysqli $dbConnection, array $categories): array
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = handleAddLotForm($postData, $fileData, $dbConnection, $categories);

        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit;
        }

        return [
            'content' => $result['content']
        ];
    }

    return [
        'content' => includeTemplate('add.php', [
            'categories' => $categories,
        ])
    ];
}
