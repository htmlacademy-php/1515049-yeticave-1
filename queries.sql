USE yeticave;

INSERT INTO categories (symbol_code, name)
VALUES ('boards', 'Доски и лыжи'),
       ('attachment', 'Крепления'),
       ('boots', 'Ботинки'),
       ('clothing', 'Одежда'),
       ('tools', 'Инструменты'),
       ('other', 'Разное');

INSERT INTO users (name, email, password, contacts)
VALUES ('Алексей Непойранов', 'Nepoiranov@mail.ru', 'password1', 123123123),
       ('Ярослав Непойранов', 'Yarik@gmail.ru', 'password12', 12312312345);

INSERT INTO lots (title, image_url, start_price, ended_at, author_id, category_id)
VALUES ('2014 Rossignol District Snowboard', 'uploads/lot-1.jpg', 10999, '2023-06-25', 1, 1),
       ('DC Ply Mens 2016/2017 Snowboard', 'uploads/lot-2.jpg', 159999, '2023-06-26', 1, 1),
       ('Крепления Union Contact Pro 2015 года размер L/XL', 'uploads/lot-3.jpg', 8000, '2023-06-27', 2, 2),
       ('Ботинки для сноуборда DC Mutiny Charocal', 'uploads/lot-4.jpg', 10999, '2023-06-28', 2, 3),
       ('Куртка для сноуборда DC Mutiny Charocal', 'uploads/lot-5.jpg', 7500, '2023-06-29', 1, 4),
       ('Маска Oakley Canopy', 'uploads/lot-6.jpg', 5400, '2023-06-30', 2, 6);

INSERT INTO rates (amount, user_id, lot_id)
VALUES (5000, 1, 2),
       (2000, 2, 5);

# получаем все категории
SELECT *
FROM categories;

# получить самые новые, открытые лоты, каждый лот включает название, стартовую цену, ссылку на изображение, цену, название категории
SELECT lots.title, lots.start_price, lots.image_url, categories.name
FROM lots
       JOIN categories ON categories.id = lots.category_id;

# показываем лот по его ID, получаем название категории, к которой принадлежит лот
SELECT lots.*, categories.name
FROM lots
       JOIN categories ON categories.id = lots.category_id
WHERE lots.id = 3;

# обновляем название лота по его идентификатору
UPDATE lots
SET title = 'Маска прикольная'
WHERE id = 6;

# получаем список ставок для лота по его идентификатору с сортировкой по дате
SELECT rates.created_at, rates.amount, lots.title, users.name
FROM rates
       JOIN lots ON lots.id = rates.lot_id
       JOIN users ON users.id = rates.user_id
WHERE lots.id = 2
ORDER BY rates.created_at DESC;
